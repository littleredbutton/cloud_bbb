module.exports = {
	extends: ['@commitlint/config-conventional'],
	rules: {
		'type-enum': [
			2,
			'always',
			[
				'l10n',
				'release',
				'build',
				'ci',
				'chore',
				'docs',
				'feat',
				'fix',
				'perf',
				'refactor',
				'revert',
				'style',
				'test',
				'example',
			],
		],
		'body-max-line-length': [
			1,
			'always',
			100,
		],
	},
	ignores: [commit => commit.startsWith('[tx-robot]')],
};