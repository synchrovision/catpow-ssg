/* globals module require*/
const path=require('path');
module.exports=()=>{
	return {
		output: {
			environment: {
				arrowFunction: false
			},
		},
		module: {
			rules: [
				{
					test: /\.jsx$/,
					exclude: /node_modules/,
					use: [
						{
							loader: 'babel-loader',
							options: {
								presets: [['@babel/preset-env', { modules: false }]]
							}
						}
					]
				},
				{
					test: /\.scss$/i,
					use: [
						'style-loader',
						{
							loader:'css-loader',
							options:{
								url:false
							}
						},
						{
							loader:'sass-loader',
							options:{
								sassOptions:{
									includePaths:[
										path.resolve('../../'),
										path.resolve('../../_config'),
										path.resolve('../../_scss'),
										path.resolve('./scss')
									]
								}
							}
						}
					]
				}
			]
		},
		resolve: {
			modules: [
				path.resolve('../../_components'),
				path.resolve('./components'),
				path.resolve('./node_modules')
			]
		}
	};
};