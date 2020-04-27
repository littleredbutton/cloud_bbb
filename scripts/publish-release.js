require('colors').setTheme({
    verbose: 'cyan',
    warn: 'yellow',
    error: 'red',
});

const fs = require("fs");
const path = require("path");
const { Octokit } = require("@octokit/rest");
const execa = require('execa');
const git = require('simple-git/promise')();
const package = require('../package.json');

require('dotenv').config();

const commitMessage = `build: ${package.version}`;
const tagName = `v${package.version}`;
const files = [
    path.join(__dirname, 'archives', `bbb-v${package.version}.tar.gz`),
    path.join(__dirname, 'archives', `bbb-v${package.version}.tar.gz.asc`),
    path.join(__dirname, 'archives', `bbb-v${package.version}.tar.gz.ncsig`),
    path.join(__dirname, 'archives', `bbb-v${package.version}.tar.gz.sig`),
];

async function notAlreadyTagged() {
    return (await git.tags()).all.indexOf(tagName) < 0;
}

async function lastCommitNotBuild() {
    return (await git.log(['-1'])).latest.message !== commitMessage;
}

async function isMasterBranch() {
    return (await git.branch()) === 'master';
}

function hasChangeLogEntry() {
    return new Promise(resolve => {
        fs.readFile(path.join(__dirname, 'CHANGELOG.md'), function (err, data) {
            if (err) throw err;

            resolve(data.includes(`[${package.version}]`));
        });
    });
}

async function hasArchiveAndSignatures() {
    return files.map(file => fs.existsSync(file)).indexOf(false) < 0;
}

async function stageAllFiles() {
    let gitProcess = execa('git', ['add', '-u']);

    gitProcess.stdout.pipe(process.stdout);

    return gitProcess;
}

function showStagedDiff() {
    let gitProcess = execa('git', ['diff', '--staged']);

    gitProcess.stdout.pipe(process.stdout);

    return gitProcess;
}

async function keypress() {
    process.stdin.setRawMode(true);

    return new Promise(resolve => process.stdin.once('data', () => {
        process.stdin.setRawMode(false)
        resolve()
    }));
}

function commit() {
    return git.commit(commitMessage, ['-S']);
}

function push() {
    return git.push('origin', 'master');
}

async function createGithubRelease() {
    const octokit = new Octokit({
        auth: process.env.GITHUB_TOKEN,
        userAgent: 'custom releaser for sualko/cloud_bbb',
    });

    let matches = (await git.remote(['get-url', 'origin'])).match(/^git@github\.com:(.+)\/(.+)\.git$/);

    if (!matches) {
        throw 'Origin is not configured or no ssh url';
    }

    const owner = matches[1];
    const repo = matches[2];

    let releaseResponse = await octokit.repos.createRelease({
        owner,
        repo,
        tag_name: tagName,
        name: tagName,
        body: '', //@TODO
        draft: true,
        prerelease: !/^\d+\.\d+\.\d+$/.test(package.version),
    });

    console.log('Draft created, see ' + releaseResponse.data.html_url);

    files.forEach(async file => {
        let assetResponse = await octokit.repos.uploadReleaseAsset({
            owner,
            repo,
            release_id: releaseResponse.data.id,
            data: fs.createReadStream(file),
            name: path.basename(file),
        });

        console.log('Asset uploaded: ' + assetResponse.data.name);
    })
}

(async () => {
    await notAlreadyTagged();
    console.log(`✔ not already tagged`.green);

    await lastCommitNotBuild();
    console.log(`✔ last commit is no build commit`.green);

    await isMasterBranch();
    console.log(`✔ this is the master branch`.green);

    await hasChangeLogEntry();
    console.log(`✔ there is a change log entry for this version`.green);

    await hasArchiveAndSignatures();
    console.log(`✔ found archive and signatures`.green);

    await stageAllFiles();
    console.log(`✔ all files staged`.green);

    await showStagedDiff();

    console.log('Press any key to continue...');
    await keypress();

    await commit();
    console.log(`✔ All files commited`.green);

    console.log('Press any key to continue...');
    await keypress();

    await push();
    console.log(`✔ All commits pushed`.green);

    await createGithubRelease();
    console.log(`✔ released on github`.green);
})();

// create changelog
// upload nextcloud app store