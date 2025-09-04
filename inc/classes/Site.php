<?php
namespace Catpow;
class Site{
	public $info,$sitemap;
	private $patterns;
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
		static $cache=[];
		$sitemap=$this->sitemap;
		if(isset($sitemap[$uri])){return $sitemap[$uri];}
		if(isset($cache[$uri])){return $cache[$uri];}

		$normalized_uri=self::normalize_uri($uri);
		if(isset($sitemap[$normalized_uri])){return $cache[$uri]=$sitemap[$normalized_uri];}
		
		if(substr($uri,-1)==='/'){
			if(!empty($info=$sitemap[$uri.'index.html']??null)){return $cache[$uri]=$info;}
			if(!empty($info=$sitemap[$uri.'index.php']??null)){return $cache[$uri]=$info;}
		}
		
		if($info_file=self::get_config_file_for_uri($uri,'info')){
			if(substr($uri,-1)!=='/' && !preg_match('/\.[a-z]+$/',$uri)){$uri.='/';}
			return $cache[$uri]=array_merge(['uri'=>$uri],(function($uri)use($info_file){return include $info_file;})($uri));
		}
		
		foreach($this->get_patterns() as $pattern=>$info){
			if(fnmatch($pattern,$uri)){
				$dir=dirname($uri);
				if(basename($pattern)==='*' && $index_file=self::get_config_file_for_uri($dir,'index')){
					$index=(function()use($index_file,$uri){return include $index_file;})();
					$basename=basename($normalized_uri);
					if(isset($index[$basename])){
						$info=array_merge($info,['uri'=>$normalized_uri],$index[$basename]);
					}
				}
				return $cache[$uri]=$info;
			}
		}
		return null;
	}
	public function get_raw_page_info($uri){
		$sitemap=$this->sitemap;
		if(isset($sitemap[$uri])){return $sitemap[$uri];}

		$normalized_uri=self::normalize_uri($uri);
		if(isset($sitemap[$normalized_uri])){return $sitemap[$normalized_uri];}
		
		if(substr($uri,-1)==='/'){
			if(!empty($info=$sitemap[$uri.'index.html']??null)){return $info;}
			if(!empty($info=$sitemap[$uri.'index.php']??null)){return $info;}
		}
		foreach($this->get_patterns() as $pattern=>$info){
			if(fnmatch($pattern,$uri)){return $sitemap[$pattern];}
		}
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
	public static function normalize_uri($uri){
		if(substr($uri,-1)==='/'){return $uri;}
		if(strpos(basename($uri),'.')===false){return $uri.'/';}
		return preg_replace('/\/index\.[html?|php]$/','/',$uri);
	}
	public function init_relation(){
		static $done=false;
		if($done){return;}
		foreach($this->sitemap as $uri=>$info){
			$parent_uri=$this->get_parent_uri($uri);
			if(!isset($info['parent'])){$this->sitemap[$uri]['parent']=$parent_uri;}
			if(isset($parent_uri) && isset($this->sitemap[$parent_uri])){
				$this->sitemap[$parent_uri]['children'][]=$uri;
			}
		}
		$done=true;
	}
	public function get_patterns(){
		static $cache;
		if(isset($cache)){return $cache;}
		return $cache=array_filter($this->sitemap,fn($key)=>strpos($key,'*')!==false,\ARRAY_FILTER_USE_KEY);
	}
	public function get_tree($root_uri){
		static $cache=[];
		if(isset($cache[$root_uri])){return $cache[$root_uri];}
		$this->init_relation();
		$normalized_root_uri=self::normalize_uri($root_uri);
		$tree=$this->get_raw_page_info($normalized_root_uri);
		$tree['uri']=$normalized_root_uri;
		$root_dir=substr($normalized_root_uri,-1)==='/'?rtrim($normalized_root_uri,'/'):dirname($normalized_root_uri);
		if(!empty($tree['children'])){
			$children=[];
			foreach($tree['children'] as $child_uri){
				if(basename($child_uri)==='*'){
					$dir=fnmatch(dirname($child_uri),$root_dir)?$root_dir:dirname($child_uri);
					if(strpos($dir,'*')===false){
						if($index_file=self::get_config_file_for_uri($dir,'index')){
							$index=(function($uri)use($index_file){return include $index_file;})($dir);
							foreach($index as $fname=>$info){
								$children[]=array_merge($this->get_tree($dir.'/'.$fname),$info);
							}
						}
					}
					else{
						$chunks=explode('/*',$dir);
						$tmp=[[$chunks[0]]];
						foreach($chunks as $i=>$chunk){
							$tmp[$i+1]=[];
							foreach($tmp[$i] as $chunk_uri){
								if($index_file=self::get_config_file_for_uri($chunk_uri,'index')){
									$index=(function($uri)use($index_file){return include $index_file;})($chunk_uri);
									foreach($index as $fname=>$info){
										$tmp[$i+1][]=$chunk_uri.'/'.$fname;
									}
								}
							}
						}
						foreach(end($tmp) as $result_uri){
							$chidlren[]=$this->get_tree($result_uri);
						}
					}
				}
				else{
					$children[]=$this->get_tree($child_uri);
				}
			}
			$tree['children']=$children;
		}
		return $cache[$root_uri]=$cache[$normalized_root_uri]=$tree;
	}
	public function get_parent_uri($uri){
		static $cache=[];
		if(isset($cache[$uri])){return $cache[$uri];}
		if(isset($this->sitemap[$uri]['parent'])){return $cache[$uri]=$this->sitemap[$uri]['parent'];}

		$parent_uri=dirname($uri);
		do{
			if(isset($this->sitemap[$parent_uri])){return $cache[$uri]=$parent_uri;}
			if(isset($this->sitemap[$parent_uri.'/'])){return $cache[$uri]=$parent_uri.'/';}
			$parent_uri=dirname($parent_uri);
		}
		while(!empty($parent_uri) && $parent_uri!=='.' && $parent_uri!=='/');
		if($parent_uri==='/'){return $cache[$uri]=$parent_uri;}
		return null;
	}
	public function __get($name){
		if($name==='patterns'){
			if(isset($this->patterns)){return $this->patterns;}
			return $this->patterns=$this->get_patterns();
		}
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