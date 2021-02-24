/* eslint-disable @typescript-eslint/no-var-requires */
const fs = require('fs');
const path = require('path');
const simpleGit = require('simple-git/promise');
const inquirer = require('inquirer');

const git = simpleGit();

async function generateChangelog(version) {
	const latestTag = (await git.tags()).latest;
	const title = `v${version}` === latestTag ? '[Unreleased]' : `${version} (${new Date().toISOString().split('T')[0]})`;

	const logs = await git.log({
		from: latestTag,
		to: 'HEAD',
	});

	const sections = [{
		type: 'feat',
		label: 'Added',
	}, {
		type: 'fix',
		label: 'Fixed',
	}];

	const entries = {};

	logs.all.forEach(log => {
		const match = log.message.match(/^([a-z]+)(?:\((\w+)\))?: (.+)/);

		if (!match) {
			return;
		}

		const [, type, scope, description] = match;
		const entry = { type, scope, description, issues: [] };

		if(log.body) {
			const matches = log.body.match(/(?:fix|fixes|closes?|refs?) #(\d+)/g) || [];

			for (const match of matches) {
				const [, number] = match.match(/(\d+)$/);

				entry.issues.push(number);
			}
		}

		if (!entries[type]) {
			entries[type] = [];
		}

		entries[type].push(entry);
	});

	let changeLog = `## ${title}\n`;

	function stringifyEntry(entry) {
		const issues = entry.issues.map(issue => {
			return `[#${issue}](https://github.com/sualko/cloud_bbb/issues/${issue})`;
		}).join('');

		return `- ${issues}${issues.length > 0 ? ' ' : ''}${entry.description}\n`;
	}

	sections.forEach(section => {
		if (!entries[section.type]) {
			return;
		}

		changeLog += `### ${section.label}\n`;

		entries[section.type].forEach(entry => {
			changeLog += stringifyEntry(entry);
		});

		delete entries[section.type];

		changeLog += '\n';
	});

	const miscKeys = Object.keys(entries);

	if (miscKeys && miscKeys.length > 0) {
		changeLog += '### Misc\n';

		miscKeys.forEach(type => {
			entries[type].forEach(entry => {
				changeLog += stringifyEntry(entry);
			});
		});
	}

	return changeLog;
}

async function editChangeLog(changeLog) {
	const answers = await inquirer.prompt([{
		type: 'editor',
		name: 'changeLog',
		message: 'You have now the possibility to edit the change log',
		default: changeLog,
	}]);

	return answers.changeLog;
}

async function hasChangeLogEntry(version) {
	if (!version) {
		return false;
	}

	const entry = await getChangelogEntry(version);

	if (entry.split('\n').filter(line => !!line.trim()).length < 2) {
		throw `Found no change log entry for ${version}`;
	}

	return true;
}

function getChangelogEntry(version) {
	return new Promise(resolve => {
		fs.readFile(path.join(__dirname, '..', '..', 'CHANGELOG.md'), 'utf8', function (err, data) {
			if (err) throw err;

			const releaseHeader = /^\d+\.\d+\.\d+$/.test(version) ? `## ${version}` : '## [Unreleased]';
			const lines = data.split('\n');
			const entry = [];

			let inEntry = false;

			for(const line of lines) {
				if (line.startsWith(releaseHeader)) {
					inEntry = true;
				} else if (line.startsWith('## ') && entry.length > 0) {
					inEntry = false;

					break;
				}

				if (inEntry) {
					entry.push(line);
				}
			}

			resolve(entry.join('\n'));
		});
	});
}

module.exports = {generateChangelog, editChangeLog, hasChangeLogEntry, getChangelogEntry};