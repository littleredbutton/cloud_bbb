/* eslint-disable @typescript-eslint/no-var-requires */
const colors = require('colors');
const fs = require('fs');
const path = require('path');
const https = require('https');
const execa = require('execa');
const simpleGit = require('simple-git/promise');
const inquirer = require('inquirer');
const dotenv = require('dotenv');
const { Octokit } = require('@octokit/rest');
const { getChangelogEntry, hasChangeLogEntry } = require('./imports/changelog');

// eslint-disable-next-line @typescript-eslint/no-var-requires
const packageInfo = require('../package.json');

colors.setTheme({
	verbose: 'cyan',
	warn: 'yellow',
	error: 'red',
});

dotenv.config();

const git = simpleGit();
const isDryRun = process.argv.indexOf('--dry-run') > 1;
const commitMessage = `release: ${packageInfo.version} :tada:`;
const tagName = `v${packageInfo.version}`;
const files = [
	path.join(__dirname, '..', 'archives', `bbb-v${packageInfo.version}.tar.gz`),
	path.join(__dirname, '..', 'archives', `bbb-v${packageInfo.version}.tar.gz.asc`),
	path.join(__dirname, '..', 'archives', `bbb-v${packageInfo.version}.tar.gz.ncsig`),
	path.join(__dirname, '..', 'archives', `bbb-v${packageInfo.version}.tar.gz.sig`),
];

isDryRun && console.log('Script is executed in dry-run mode.'.verbose);

function pull() {
	return git.pull('origin', 'master');
}

async function notAlreadyTagged() {
	if ((await git.tags()).all.includes(tagName)) {
		throw 'version already tagged';
	}
}

async function lastCommitNotBuild() {
	const latest = (await git.log(['-1'])).latest || {};

	return latest.message !== commitMessage;
}

async function isMasterBranch() {
	return (await git.branch()).current === 'master';
}

async function hasArchiveAndSignatures() {
	return files.map(file => fs.existsSync(file)).indexOf(false) < 0;
}

async function stageAllFiles() {
	if (isDryRun) {
		return;
	}

	const gitProcess = execa('git', ['add', '-u']);

	gitProcess.stdout.pipe(process.stdout);

	return gitProcess;
}

function showStagedDiff() {
	const gitProcess = execa('git', ['diff', '--staged']);

	gitProcess.stdout.pipe(process.stdout);

	return gitProcess;
}

async function keypress() {
	return inquirer.prompt([{
		type: 'input',
		name: 'keypress',
		message: 'Press any key to continue... (where is the any key?)',
	}]);
}

function commit() {
	if (isDryRun) {
		return;
	}

	return git.commit(commitMessage, ['-S', '-n']);
}

async function wantToContinue(message) {
	const answers = await inquirer.prompt([{
		type: 'confirm',
		name: 'continue',
		message,
		default: false,
	}]);

	if (!answers.continue) {
		process.exit(10);
	}
}

function push() {
	if (isDryRun) {
		return;
	}

	return git.push('origin', 'master');
}

async function createGithubRelease(changeLog) {
	if (!process.env.GITHUB_TOKEN) {
		throw 'Github token missing';
	}

	const octokit = new Octokit({
		auth: process.env.GITHUB_TOKEN,
		userAgent: 'custom releaser for sualko/cloud_bbb',
	});

	const origin = (await git.remote(['get-url', 'origin'])) || '';
	const matches = origin.trim().match(/^git@github\.com:(.+)\/(.+)\.git$/);

	if (!matches) {
		throw 'Origin is not configured or no ssh url';
	}

	const owner = matches[1];
	const repo = matches[2];
	const releaseOptions = {
		owner,
		repo,
		tag_name: tagName,
		name: `BigBlueButton Integration ${tagName}`,
		body: changeLog.replace(/^## [^\n]+\n/, ''),
		prerelease: !/^\d+\.\d+\.\d+$/.test(packageInfo.version),
	};

	if (isDryRun) {
		console.log('github release options', releaseOptions);
		return [];
	}

	const releaseResponse = await octokit.repos.createRelease(releaseOptions);

	console.log(`Draft created, see ${releaseResponse.data.html_url}`.verbose);

	function getMimeType(filename) {
		if (filename.endsWith('.asc') || filename.endsWith('sig')) {
			return 'application/pgp-signature';
		}

		if (filename.endsWith('.tar.gz')) {
			return 'application/gzip';
		}

		if (filename.endsWith('.ncsig')) {
			return 'text/plain';
		}

		return 'application/octet-stream';
	}

	const assetUrls = await Promise.all(files.map(async file => {
		const filename = path.basename(file);
		const uploadOptions = {
			owner,
			repo,
			release_id: releaseResponse.data.id,
			data: fs.createReadStream(file),
			headers: {
				'content-type': getMimeType(filename),
				'content-length': fs.statSync(file)['size'],
			},
			name: filename,
		};

		const assetResponse = await octokit.repos.uploadReleaseAsset(uploadOptions);

		console.log(`Asset uploaded: ${assetResponse.data.name}`.verbose);

		return assetResponse.data.browser_download_url;
	}));

	return assetUrls;
}

async function uploadToNextcloudStore(archiveUrl) {
	if(!process.env.NEXTCLOUD_TOKEN) {
		throw 'Nextcloud token missing';
	}

	const hostname = 'apps.nextcloud.com';
	const apiEndpoint = '/api/v1/apps/releases';
	const signatureFile = files.find(file => file.endsWith('.ncsig'));
	const data = JSON.stringify({
		download: archiveUrl,
		signature: fs.readFileSync(signatureFile, 'utf-8'),
		nightly: false,
	});
	const options = {
		hostname,
		port: 443,
		path: apiEndpoint,
		method: 'POST',
		headers: {
			'Content-Type': 'application/json',
			'Content-Length': data.length,
			Authorization: `Token ${process.env.NEXTCLOUD_TOKEN}`,
		},
	};

	if (isDryRun) {
		console.log('nextcloud app store request', options, data);
		return;
	}

	return new Promise((resolve, reject) => {
		const req = https.request(options, res => {
			if (res.statusCode === 200) {
				console.log('App release was updated successfully'.verbose);
				resolve();
			} else if (res.statusCode === 201) {
				console.log('App release was created successfully'.verbose);
				resolve();
			} else if (res.statusCode === 400) {
				reject('App release was not accepted');
			} else {
				reject('App release rejected with status ' + res.statusCode);
			}

			res.on('data', d => {
				process.stdout.write(d);
			});
		});

		req.on('error', error => {
			reject(error);
		});

		req.write(data);
		req.end();
	});
}

async function run() {
	await pull();
	console.log('✔ pulled latest changes'.green);

	await execa('yarn', ['composer:install:dev']);
	console.log('✔ composer dev dependencies installed'.green);

	await notAlreadyTagged();
	console.log('✔ not already tagged'.green);

	await lastCommitNotBuild();
	console.log('✔ last commit is no build commit'.green);

	await isMasterBranch();
	console.log('✔ this is the master branch'.green);

	await hasChangeLogEntry(packageInfo.version);
	console.log('✔ there is a change log entry for this version'.green);

	const changeLog = await getChangelogEntry(packageInfo.version);

	console.log(changeLog);

	console.log('Press any key to continue...');
	await keypress();

	await hasArchiveAndSignatures();
	console.log('✔ found archive and signatures'.green);

	await stageAllFiles();
	console.log('✔ all files staged'.green);

	await showStagedDiff();

	await wantToContinue('Should I commit those changes?');

	await commit();
	console.log('✔ All files commited'.green);

	await wantToContinue('Should I push all pending commits?');

	await push();
	console.log('✔ All commits pushed'.green);

	await wantToContinue('Should I continue to create a Github release?');

	const assetUrls = await createGithubRelease(changeLog);
	console.log('✔ released on github'.green);

	const archiveAssetUrl = assetUrls.find(url => url.endsWith('.tar.gz'));
	console.log(`Asset url for Nextcloud store: ${archiveAssetUrl}`.verbose);

	await wantToContinue('Should I continue to upload the release to the app store?');

	await uploadToNextcloudStore(archiveAssetUrl);
	console.log('✔ released in Nextcloud app store'.green);
}

run().catch(err => {
	console.log(`✘ ${err.toString()}`.error);
});
