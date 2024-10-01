<?php
namespace Catpow\API;
use Catpow\JSX;
class Modules{
	public static function request($req){
		JSX::init();
		exec('node modules/script/declaration.mjs -t module -v 2020 '.implode(' ',glob(\INC_DIR.'/modules/*/index.js')),$output);
		return json_decode(implode("\n",$output));
	}
}