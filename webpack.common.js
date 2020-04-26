const path = require('path')

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
				test: /\.(js)$/,
				use: 'eslint-loader',
				enforce: 'pre',
			},
			{
				test: /\.js$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
			},
			{
				test: /\.(png|jpg|gif|svg)$/,
				loader: 'url-loader',
				options: {
					name: '[name].[ext]?[hash]',
					limit: 8192,
				},
			},
		],
	},
	plugins: [
	],
	resolve: {
		extensions: ['*', '.tsx', '.ts', '.js', '.scss'],
		symlinks: false,
	},
}
