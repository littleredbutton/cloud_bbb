/* eslint-disable @typescript-eslint/no-var-requires */
process.env.npm_package_name = 'bbb';

const path = require('path');
const ESLintPlugin = require('eslint-webpack-plugin');
const webpackConfig = require('@nextcloud/webpack-vue-config')
const webpackRules = require('@nextcloud/webpack-vue-config/rules')

webpackRules.RULE_TSX = {
	test: /\.tsx?$/,
	use: [
		{
			loader: 'babel-loader',
			options: {
				babelrc: false,
			},
		},
		'ts-loader',
	],
};
webpackRules.RULE_RAW = {
	test: /\.svg$/,
	resourceQuery: /raw/,
	type: 'asset/source'
};

webpackConfig.entry = {
		admin: [
			path.join(__dirname, 'ts', 'admin.ts'),
		],
		filelist: [
			path.join(__dirname, 'ts', 'filelist.ts'),
		],
		manager: [
			path.join(__dirname, 'ts', 'Manager', 'index.tsx'),
		],
		restrictions: [
			path.join(__dirname, 'ts', 'Restrictions', 'index.tsx'),
		],
		join: [
			path.join(__dirname, 'ts', 'join.ts'),
		],
		waiting: [
			path.join(__dirname, 'ts', 'waiting.ts'),
		],
	};

webpackConfig.module.rules = Object.values(webpackRules);

webpackConfig.plugins.push(new ESLintPlugin());

webpackConfig.resolve.extensions = [...webpackConfig.resolve.extensions, '.jsx', '.ts', '.tsx'];

module.exports = webpackConfig
