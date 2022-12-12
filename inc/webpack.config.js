/* globals module require*/
const path = require('path');
module.exports = () => {
	return {
		output: {
			environment: {
				arrowFunction: false
			},
		},
		module: {
			rules: [{
					test: /\.jsx$/,
					exclude: /node_modules/,
					use: [{
						loader: 'babel-loader',
						options: {
							presets: [
								['@babel/preset-env', {
									modules: false
								}]
							],
							plugins: ['@babel/plugin-transform-runtime']
						}
					}]
				},
				{
					test: /\.tsx?$/,
					loader: "ts-loader"
				},
				{
					test: /\.scss$/i,
					use: [
						'style-loader',
						{
							loader: 'css-loader',
							options: {
								url: false
							}
						},
						{
							loader: 'sass-loader',
							options: {
								sassOptions: {
									includePaths: [
										path.resolve('../../'),
										path.resolve('../../_config'),
										path.resolve('../../_scss'),
										path.resolve('./scss')
									]
								}
							}
						}
					]
				},
				{
					test: /\.svg$/,
					use: [{
							loader: require.resolve('@svgr/webpack'),
							options: {
								prettier: false,
								svgo: false,
								svgoConfig: {
									plugins: [{
										removeViewBox: false
									}],
								},
								titleProp: true,
								ref: true,
							},
						},
						{
							loader: require.resolve('file-loader'),
							options: {
								name: 'static/media/[name].[hash].[ext]',
							},
						},
					],
					issuer: {
						and: [/\.(ts|tsx|js|jsx|md|mdx)$/],
					},
				}
			]
		},
		resolve: {
			extensions: ["", ".ts", ".tsx", ".js", ".jsx"],
			modules: [
				path.resolve('../../modules'),
				path.resolve('./modules'),
				path.resolve('./node_modules'),
				path.resolve('../../node_modules')
			]
		}
	};
};
