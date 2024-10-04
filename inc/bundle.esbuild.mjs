import * as esbuild from 'esbuild';
import svgr from 'esbuild-plugin-svgr';
import inlineImportPlugin from 'esbuild-plugin-inline-import';


let pathResolver={
	name:'pathResolver',
	setup(build) {
		build.onResolve({filter:/^@?\w/},async(args)=>{
			for(const resolveDir of ['./modules','./node_modules']){
				const result=await build.resolve('./'+args.path,{
					kind:'import-statement',resolveDir
				});
				if(result.errors.length===0){return {path:result.path};}
			}
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