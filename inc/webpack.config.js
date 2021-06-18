/* globals module require*/
const path=require('path');
module.exports=()=>{
	return {
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