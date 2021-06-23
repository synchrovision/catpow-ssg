<?php
namespace Catpow;
class Tmpl{
	public static function compile_for_file($file){
		$uri=preg_replace('/index\.(html?|php)$/','',str_replace(ABSPATH,'',$file));
		if(($tmpl_file=self::get_tmpl_file_for_file($file)) || ($tmpl_file=self::get_tmpl_file_for_uri($uri))){
			ob_start();
			Page::init($uri);
			try{
				global $sitemap,$page;
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
		global $sitemap;
		if(file_exists($f=$file.'.tmpl.php')){return $f;}
		if(file_exists($f=str_replace(ABSPATH,TMPL_DIR,$file).'.php')){return $f;}
		return false;
	}
	public static function get_tmpl_file_for_uri($uri){
		global $sitemap;
		if(empty($sitemap[$uri]['template'])){return false;}
		if(file_exists($f=ABSPATH.$sitemap[$uri]['template'])){return $f;}
		if(file_exists($f=TMPL_DIR.$sitemap[$uri]['template'])){return $f;}
		return false;
	}
}