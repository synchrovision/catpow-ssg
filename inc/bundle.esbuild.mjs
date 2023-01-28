import * as esbuild from 'esbuild';
import {svgrPlugin} from 'esbuild-svgr-plugin';
import {sassPlugin} from 'esbuild-sass-plugin';

let pathResolver={
	name:'pathResolver',
	setup(build) {
		build.onResolve({filter: /^(util|component)$/},async(args)=>{
			const result=await build.resolve('./'+args.path,{
				kind:'import-statement',
				resolveDir:'./modules',
			});
			if(result.errors.length>0){
				return {errors:result.errors};
			}
			return {path:result.path};
		});
		build.onResolve({filter: /^\w/},async(args)=>{
			const result=await build.resolve('./'+args.path,{
				kind:'import-statement',
				resolveDir:'./node_modules',
			});
			if(result.errors.length>0){
				return {errors:result.errors};
			}
			return {path:result.path};
		});
	},
}

await esbuild.build({
	entryPoints: [process.argv[2]],
	outfile: process.argv[3],
	bundle:true,
	plugins:[pathResolver,svgrPlugin(),sassPlugin()]
})