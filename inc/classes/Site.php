<?php
namespace Catpow;
class Site{
	public $info;
	private static $instance;
	private function __construct($info){
		$this->info=$info;
	}
	public static function init($info=null){
		if(empty($info) && file_exists($site_config_file=CONF_DIR.'/site_config.php')){
			global $sitemap;
			include($site_config_file);
			if(isset($site)){$info=$site;}
		}
		return $GLOBALS['site']=static::$instance=new static($info);
	}
	public function __get($name){
		if(isset($this->info[$name])){return $this->info[$name];}
	}
	public static function get_instance(){
		if(empty($GLOBALS['site'])){static::init();}
		return $GLOBALS['site'];
	}
	public static function copy_file_from_remote_if_not_exists($uri){
		if(
			file_exists(ABSPATH.$uri) || 
			file_exists(TMPL_DIR.$uri)
		){return false;}
		$site=self::get_instance();
		if(!is_null($site->url)){
			$url=$site->url.$uri;
			$ch=curl_init($url);
			curl_setopt($ch,CURLOPT_NOBODY,true);
			curl_exec($ch);
			$responseCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
			curl_close($ch);
			if($responseCode===200){
				$dir=ABSPATH.substr($uri,0,strrpos($uri,'/'));
				if(!is_dir($dir)){mkdir($dir,0755,true);}
				copy($url,ABSPATH.$uri);
			}
		}
	}
}