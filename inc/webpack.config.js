/* globals module */
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
		}
	};
};