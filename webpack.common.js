/* eslint-disable @typescript-eslint/no-var-requires */
const path = require('path');
const ESLintPlugin = require('eslint-webpack-plugin');

module.exports = {
	entry: {
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
	},
	output: {
		path: path.resolve(__dirname, './js'),
		publicPath: '/js/',
		filename: '[name].js',
		chunkFilename: 'chunks/[name]-[hash].js',
	},
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				use: [
					{
						loader: 'babel-loader',
						options: {
							babelrc: false,
							plugins: ['react-hot-loader/babel'],
						},
					},
					'ts-loader',
				],
			},
			{
				test: /\.css$/,
				use: ['style-loader', 'css-loader'],
			},
			{
				test: /\.scss$/,
				use: ['style-loader', 'css-loader', 'sass-loader'],
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
			{
				test: /\.(png|jpg|gif|svg)$/,
				type: 'asset',
				generator: {
					filename: 'static/[name][ext]?[hash]',
				},
			},
		],
	},
	plugins: [
		new ESLintPlugin(),
	],
	resolve: {
		extensions: ['*', '.tsx', '.ts', '.js', '.scss'],
		symlinks: false,
	},
};
