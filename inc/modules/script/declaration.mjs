import fs from 'fs';
import { resolve } from 'node:path';
import { parseArgs } from 'node:util';
import * as acorn from 'acorn';
import jsx from 'acorn-jsx';


const {values:options,positionals:files} = parseArgs({allowPositionals:true,options:{
	sourceType:{
		type:'string',
		short:'t',
		default:'module'
	},
	ecmaVersion:{
		type:'string',
		short:'v',
		default:'2020'
	}
}});
if(files.length===0){
	files.push('modules/script/test.jsx');
}

const results={};
for(const file of files){
	extractFunctions(file,options,results);
}
fs.writeSync(1,JSON.stringify(results));

function extractFunctions(file,options,results){
	if(results[file]!=null){return;}
	results[file]={};
	if(options.sourceType==='script'){
		const code=fs.readFileSync(file);
		const ast=acorn.parse(code, options);
		ast.body.forEach((token)=>{
			if(token.type==='FunctionDeclaration'){
				results[file][token.id.name]=getFunctionInfo(token);
			}
		});
	}
	else{
		extractExportedFunctionsRecursive(file,options,results[file]);
	}
}
function extractExportedFunctionsRecursive(file,options,functions,importMap){
	const code=fs.readFileSync(file);
	const ast=acorn.Parser.extend(jsx()).parse(code, options);
	const exportedFunctions={};
	ast.body.forEach((token)=>{
		switch(token.type){
			case 'ExportAllDeclaration':{
				extractExportedFunctionsRecursive(resolve(file,'../',token.source.value),options,exportedFunctions);
				break;
			}
			case 'ExportNamedDeclaration':{
				if(token.declaration!=null && token.declaration.declarations!=null){
					for(const declaration of token.declaration.declarations){
						if(declaration.init.type==='ArrowFunctionExpression' || declaration.init.type==='FunctionDeclaration'){
							exportedFunctions[declaration.id.name]=getFunctionInfo(declaration.init);
						}
					}
				}
				else if(token.specifiers){
					extractExportedFunctionsRecursive(resolve(file,'../',token.source.value),options,exportedFunctions,token.specifiers.reduce((p,c)=>p[c.local.name]=c.exported.name,{}));
				}
				
				break;
			}
			case 'ExportDefaultDeclaration':{
				if(token.declaration.type==='ArrowFunctionExpression' || token.declaration.type==='FunctionDeclaration'){
					exportedFunctions['[default]']=getFunctionInfo(declaration);
				}
				break;
			}
		}
	});
	if(importMap!=null){
		for(const key in importMap){
			if(exportedFunctions[key]!=null){
				functions[importMap[key]]=exportedFunctions[key];
			}
		}
	}
	else{
		Object.assign(functions,exportedFunctions);
	}
}
function getFunctionInfo(token){
	return {
		params:token.params.map((param)=>{
			if(param.type==='AssignmentPattern'){
				return {name:param.left.name,default:param.right.value};
			}
			return {name:param.name};
		})
	};
}