<?php
namespace Catpow\API;
use Catpow\JSX;
class Modules{
	public static function request($req){
		JSX::init();
		$files=[];
		foreach(scandir(INC_DIR.'/modules/src/') as $fname){
			if(substr($fname,0,1)!=='.' && file_exists(INC_DIR.'/modules/src/'.$fname.'/index.js')){
				$files[]='modules/src/'.$fname.'/index.js';
			}
		}
		exec('node modules/src/script/declaration.mjs -t module '.implode(' ',$files),$output);
		return json_decode(implode("\n",$output));
	}
}