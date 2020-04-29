/* eslint-disable @typescript-eslint/no-var-requires */
require('colors').setTheme({
	verbose: 'cyan',
	warn: 'yellow',
	error: 'red',
});

const fs = require('fs');
const path = require('path');
const libxml = require('libxmljs');
const https = require('https');
const archiver = require('archiver');
const execa = require('execa');
const git = require('simple-git/promise')();
const package = require('../package.json');

const infoXmlPath = './appinfo/info.xml';
const isStableRelease = process.argv.indexOf('--stable') > 1;

async function getVersion() {
	return package.version + (!isStableRelease ? '-git.' + (await git.raw(['rev-parse', '--short', 'HEAD'])).trim() : '');
}

run();

async function run() {
	const appId = await prepareInfoXml();
	await createRelease(appId);
}

async function prepareInfoXml() {
	const infoFile = fs.readFileSync(infoXmlPath);
	const xmlDoc = libxml.parseXml(infoFile);

	updateVersion(xmlDoc, await getVersion());
	await validateXml(xmlDoc);

	return xmlDoc.get('//id').text();
}

function updateVersion(xmlDoc, version) {
	const versionChild = xmlDoc.get('//version');
	const currentVersion = versionChild.text();

	if (version !== currentVersion) {
		console.log(`✔ Update version in info.xml to ${version}.`.green);

		versionChild.text(version);

		fs.writeFileSync(infoXmlPath, xmlDoc.toString());
	}
}

async function createRelease(appId) {
	const version = await getVersion();
	console.log(`I'm now building ${appId} in version ${version}.`.verbose);

	await execa('yarn', ['composer:install:dev']);
	console.log('✔ composer dev dependencies installed'.green);

	await execa('yarn', ['lint']);
	console.log('✔ linters are happy'.green);

	await execa('yarn', ['composer:install']);
	console.log('✔ composer dependencies installed'.green);

	await execa('yarn', ['build']);
	console.log('✔ scripts built'.green);

	const filePath = await createArchive(appId, appId + '-v' + version);
	await createNextcloudSignature(appId, filePath);
	await createGPGSignature(filePath);
	await createGPGArmorSignature(filePath);
}


function createArchive(appId, fileBaseName) {
	const fileName = `${fileBaseName}.tar.gz`;
	const filePath = path.normalize(__dirname + `/../archives/${fileName}`);
	const output = fs.createWriteStream(filePath);
	const archive = archiver('tar', {
		gzip: true,
	});

	archive.on('warning', function (err) {
		if (err.code === 'ENOENT') {
			console.warn('Archive warning: '.warn, err);
		} else {
			throw err;
		}
	});

	archive.on('error', function (err) {
		throw err;
	});

	archive.pipe(output);

	function addDirectory(name) {
		archive.directory(name + '/', `${appId}/${name}/`);
	}

	function addFile(name) {
		archive.file(name, { name: `${appId}/${name}` });
	}

	addDirectory('appinfo');
	addDirectory('img');
	addDirectory('js'),
	addDirectory('lib');
	addDirectory('templates');
	addFile('COPYING');
	addFile('README.md');

	archive.glob('vendor/**/*', {
		ignore: ['.git'],
	}, {
		prefix: appId,
	});

	return new Promise(resolve => {
		output.on('close', function () {
			console.log(`✔ Wrote ${archive.pointer()} bytes to ${fileName}`.green);

			resolve(filePath);
		});

		archive.finalize();
	});
}

function createNextcloudSignature(appId, filePath) {
	const {
		exec,
	} = require('child_process');

	return new Promise((resolve, reject) => {
		const sigPath = filePath + '.ncsig';
		exec(`openssl dgst -sha512 -sign ~/.nextcloud/certificates/${appId}.key ${filePath} | openssl base64 > ${sigPath}`, (error, stdout, stderr) => {
			if (error) {
				throw error;
			}

			if (stdout) {
				console.log(`stdout: ${stdout}`);
			}

			if (stderr) {
				console.log(`stderr: ${stderr}`);
			}

			console.log(`✔ created Nextcloud signature: ${path.basename(sigPath)}`.green);

			resolve();
		});
	});
}

function createGPGSignature(filePath) {
	const {
		exec,
	} = require('child_process');

	return new Promise((resolve, reject) => {
		exec(`gpg --yes --detach-sign "${filePath}"`, (error, stdout, stderr) => {
			if (error) {
				throw error;
			}

			if (stdout) {
				console.log(`stdout: ${stdout}`);
			}

			if (stderr) {
				console.log(`stderr: ${stderr}`);
			}

			console.log(`✔ created detached signature: ${path.basename(filePath)}.sig`.green);

			resolve();
		});
	});
}

function createGPGArmorSignature(filePath) {
	const {
		exec,
	} = require('child_process');

	return new Promise((resolve, reject) => {
		exec(`gpg --yes --detach-sign --armor "${filePath}"`, (error, stdout, stderr) => {
			if (error) {
				throw error;
			}

			if (stdout) {
				console.log(`stdout: ${stdout}`);
			}

			if (stderr) {
				console.log(`stderr: ${stderr}`);
			}

			console.log(`✔ created detached signature: ${path.basename(filePath)}.asc`.green);

			resolve();
		});
	});
}

async function validateXml(xmlDoc) {
	const schemaLocation = xmlDoc.root().attr('noNamespaceSchemaLocation').value();

	if (!schemaLocation) {
		throw 'Found no schema location';
	}


	let schemaString;
	try {
		schemaString = await wget(schemaLocation);
	} catch (err) {
		console.log('Could not download schema. Skip validation.'.warn);

		return;
	}
	const xsdDoc = libxml.parseXml(schemaString);

	if (xmlDoc.validate(xsdDoc)) {
		console.log('✔ Document valid'.green);
	} else {
		console.log('✘ Document INVALID'.error);

		xmlDoc.validationErrors.forEach((error, index) => {
			console.log(`#${index + 1}\t${error.toString().trim()}`.warn);
			console.log(`\tLine ${error.line}:${error.column} (level ${error.level})`.verbose);
		});

		throw 'Abort';
	}
}

function wget(url) {
	return new Promise((resolve, reject) => {
		https.get(url, (resp) => {
			let data = '';

			resp.on('data', (chunk) => {
				data += chunk;
			});

			resp.on('end', () => {
				resolve(data);
			});

		}).on('error', (err) => {
			reject(err);
		});
	});
}