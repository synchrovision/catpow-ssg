<?php
namespace Catpow;
class Tmpl{
	public static function compile_for_file($file){
		if($tmpl_file=self::get_tmpl_file_for_file($file)){
			ob_start();
			try{
				include $tmpl_file;
				if(!is_dir(dirname($file))){
					mkdir(dirname($file),0755,true);
				}
				file_put_contents($file,ob_get_clean());
			}
			catch(\Error $e){
				ob_end_clean();
				error_log($e->getMessage());
			}
		}
	}
	public static function get_tmpl_file_for_file($file){
		if(file_exists($f=$file.'.tmpl.php')){return $f;}
		if(file_exists($f=str_replace(ABSPATH,TMPL_DIR,$file).'.php')){return $f;}
		return false;
	}
}