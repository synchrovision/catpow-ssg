<?php
namespace Catpow;
class Jsx{
	public static function compile_for_file($file){
		if($jsx_file=self::get_jsx_file_for_file($file)){
			self::compile($jsx_file,$file);
		}
	}
	public static function get_jsx_file_for_file($file){
		$jsx_file=substr($file,0,-2).'jsx';
		if(file_exists($jsx_file)){return $jsx_file;}
		if(file_exists($f=str_replace('/js/','/_jsx/',$jsx_file))){return $f;}
		if(file_exists($f=str_replace(ABSPATH,TMPL_DIR,$jsx_file))){return $f;}
		if(file_exists($f=str_replace([ABSPATH,'/js/'],[TMPL_DIR,'/_jsx/'],$jsx_file))){return $f;}
		return false;
	}
	public static function compile($jsx_file,$js_file){
		putenv('PATH='.getenv('PATH').':'.INC_DIR.':'.INC_DIR.'/node_modules/.bin');
		putenv('NODE_PATH='.getenv('NODE_PATH').':'.INC_DIR.'/node_modules');
		chdir(INC_DIR);
		if(!file_exists(INC_DIR.'/node_modules')){passthru('npm install');}
		if(!file_exists($jsx_file)){return;}
		if(!file_exists($js_file) or filemtime($js_file) < filemtime($jsx_file)){
			passthru('babel '.$jsx_file.' -o '.$js_file);
		}
	}
}