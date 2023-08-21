import * as esbuild from 'esbuild';
import fs from 'fs';
import path from 'path';
import {transform } from '@svgr/core';
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
let svgAsJsx={
	name:'svgAsJsx',
	setup(build){
		build.onResolve({filter:/\.svg$/},(args)=>{
			return {
				path:path.join(args.resolveDir,args.path),
				namespace:/\.[jt]sx?$/.test(args.importer)?'svgr':undefined,
			}
		})

		build.onLoad({filter:/.*/,namespace:'svgr'},async({path:pathname})=>{
			const [filename]=pathname.split('?',2)
			const dirname=path.dirname(filename)
			const svg=await fs.promises.readFile(pathname,'utf8')
			const contents=await transform(svg,{jsxRuntime:'automatic'},{filePath:pathname})

			return {
				contents,
				loader: 'jsx',
				resolveDir:dirname,
			}
		})
	},
}

await esbuild.build({
	entryPoints: [process.argv[2]],
	outfile: process.argv[3],
	bundle:true,
	plugins:[
		pathResolver,
		svgAsJsx,
		sassPlugin({
			type:'style',
			loadPaths:['../../','../../_config','../../_tmpl','./scss']
		})
	]
})