<?php
namespace Catpow\API;
use Catpow\JSX;
class Modules{
	public static function request($req){
		JSX::init();
		$files=[];
		$file_to_module_name=[];
		foreach(scandir(INC_DIR.'/node_modules-included/catpow/src/') as $fname){
			if(substr($fname,0,1)!=='.' && file_exists(INC_DIR.'/node_modules-included/catpow/src/'.$fname.'/index.js')){
				$files[]=$file=INC_DIR.'/node_modules-included/catpow/src/'.$fname.'/index.js';
				$file_to_module_name[$file]=$fname;
			}
		}
		exec('node node_modules-included/catpow/src/script/declaration.mjs -t module '.implode(' ',$files),$output);
		$declaration=json_decode(implode("\n",$output),true);
		$modules=[];
		foreach($file_to_module_name as $file=>$module_name){
			$modules[$module_name]=$declaration[$file];
		}
		return $modules;
	}
}