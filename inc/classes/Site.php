<?php
namespace Catpow;
class Site{
	public $info,$sitemap;
	private static $instance;
	private function __construct($info,$sitemap){
		$this->info=$info;
		$this->sitemap=$sitemap;
	}
	public static function init($info=null,$sitemap=null){
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
		if($info_file=self::get_config_file_for_uri($uri,'info')){
			if(substr($uri,-1)!=='/' && !preg_match('/\.[a-z]+$/',$uri)){$uri.='/';}
			return array_merge(['uri'=>$uri],(function($uri)use($info_file){return include $info_file;})($uri));
		}
		$pathinfo=pathinfo($uri);
		if($pathinfo['filename']==='index'){
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
	public static function get_config_file_for_uri($uri,$name){
		static $cache=[];
		if(isset($cache[$uri])){
			$d=$cache[$uri];
		}
		else{
			if(substr($uri,-1)!=='/' && preg_match('/\.[a-z]+$/',$uri)){
				$uri=dirname($uri);
			}
			$dnames=explode('/',trim($uri,'/'));
			foreach([ABSPATH,TMPL_DIR] as $d){
				foreach($dnames as $dname){
					if(file_exists($tmp=$d.'/'.$dname)){$d=$tmp;continue;}
					if(file_exists($tmp=$d.'/[template]')){$d=$tmp;continue;}
					$d=false;
					continue 2;
				}
			}
		}
		if(empty($d)){return false;}
		if(file_exists($config_file=$d.'/['.$name.'].php')){return $config_file;}
		return false;
	}
	public function __get($name){
		if(isset($this->info[$name])){return $this->info[$name];}
	}
	public static function get_instance(){
		if(empty(static::$instance)){static::init();}
		return static::$instance;
	}
	public static function copy_file_from_template_if_not_exists_or_updated($uri){
		$file=ABSPATH.$uri;
		if(file_exists($tmpl_file=TMPL_DIR.$uri) || file_exists($tmpl_file=INC_DIR.$uri)){
			if(!file_exists($file) || filemtime($file)<filemtime($tmpl_file)){
				if(!is_dir(dirname($file))){
					mkdir(dirname($file),0755,true);
				}
				if(!is_dir($tmpl_file)){copy($tmpl_file,$file);}
			}
		}
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