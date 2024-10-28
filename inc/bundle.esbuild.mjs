import * as esbuild from 'esbuild';
import svgr from 'esbuild-plugin-svgr';
import inlineImportPlugin from 'esbuild-plugin-inline-import';

import {parseArgs} from 'node:util';

const {
	values,
	positionals
}=parseArgs({
	options:{
		useGlobalReact:{
			type:'boolean',
			default:false
		}
	},
	allowPositionals: true
});

const {useGlobalReact}=values;

let pathResolver={
	name:'pathResolver',
	setup(build) {
		const externalModules = new Set(build.initialOptions.external || []);
		
		build.onResolve({filter:/^catpow/},async(args)=>{
			const result=await build.resolve('./'+args.path.slice(6),{
				kind:'import-statement',resolveDir:'./modules/src'
			});
			if(result.errors.length===0){return {path:result.path};}
		});
		build.onResolve({filter:/^@?\w/},async(args)=>{
			if(useGlobalReact && (args.path==='react' || args.path==='react-dom')){
				return {
					path: args.path,
					namespace: 'react-global'
				};
			}
			if(externalModules.has(args.path)){return {path:args.path,external:true}};
			const result=await build.resolve('./'+args.path,{
				kind:'import-statement',
				resolveDir:'./node_modules'
			});
			if(result.errors.length===0){return {path:result.path};}
		});
		if(useGlobalReact){
			build.onLoad({filter:/.*/,namespace:'react-global'}, async(args)=>{
				if (args.path==='react-dom') {
					return {
						contents:'export default window.ReactDOM;',
						loader:'js',
					};
				}
				if (args.path==='react') {
					return {
						contents:'export default window.React;',
						loader:'js',
					};
				}
			});
		}
	}
}
let inlineCssImporter=inlineImportPlugin({
	filter:/css:/,
	transform:async (contents,args)=>{
		return contents;
	}
});

await esbuild.build({
	entryPoints: [positionals[0]],
	outfile: positionals[1],
	bundle:true,
	external:useGlobalReact?['react','react-dom']:[],
	define:useGlobalReact?{
		'React':'window.React',
		'React.createElement':'window.React.createElement',
		'React.Fragment':'window.React.Fragment',
		'ReactDOM':'window.ReactDOM',
		'ReactDOM.render':'window.ReactDOM.render'
	}:{},
	plugins:[
		inlineCssImporter,
		pathResolver,
		svgr(),
	]
})