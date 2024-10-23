import * as esbuild from 'esbuild';
import svgr from 'esbuild-plugin-svgr';
import inlineImportPlugin from 'esbuild-plugin-inline-import';


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
			if(externalModules.has(args.path)){return {path:args.path,external:true}};
			const result=await build.resolve('./'+args.path,{
				kind:'import-statement',
				resolveDir:'./node_modules'
			});
			if(result.errors.length===0){return {path:result.path};}
		});
	}
}
let inlineCssImporter=inlineImportPlugin({
	filter:/css:/,
	transform:async (contents,args)=>{
		return contents;
	}
});

await esbuild.build({
	entryPoints: [process.argv[2]],
	outfile: process.argv[3],
	bundle:true,
	plugins:[
		inlineCssImporter,
		pathResolver,
		svgr(),
	]
})