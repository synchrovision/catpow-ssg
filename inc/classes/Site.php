<?php
namespace Catpow;
class Site{
	public $info,$sitemap;
	private static $instance;
	private function __construct($info,$sitemap){
		$this->info=$info;
		$this->sitemap=$sitemap;
	}
	public static function init($info='site-info',$sitemap='site-sitemap'){
		if(is_string($info)){$info=csv($info)[0];}
		if(is_string($sitemap)){$sitemap=csv($sitemap)->dict('uri');}
		return $GLOBALS['site']=static::$instance=new static($info,$sitemap);
	}
	public function get_page_info($uri){
		$sitemap=$this->sitemap;
		if(isset($sitemap[$uri])){return $sitemap[$uri];}
		if(substr($uri,-1)==='/'){
			if(!empty($info=$sitemap[$uri.'index.html']??null)){return $info;}
			if(!empty($info=$sitemap[$uri.'index.php']??null)){return $info;}
		}
		$pathinfo=pathinfo($uri);
		if(['filenam']==='index'){
			if(!empty($info=$sitemap[$pathinfo['dirname'].'/']??null)){return $info;}
		}
		$dir=$pathinfo['dirname'];
		do{
			if(!empty($info=$sitemap[$dir.'/*']??null)){return $info;}
			$dir=dirname($dir);
		}
		while(!empty($dir) && $dir!=='.' && $dir!=='/');
		return null;
	}
	public function __get($name){
		if(isset($this->info[$name])){return $this->info[$name];}
	}
	public static function get_instance(){
		if(empty(static::$instance)){static::init();}
		return static::$instance;
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