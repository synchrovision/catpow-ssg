<?php
namespace Catpow;
class Tmpl{
	const SHOULD_OUTPUT=1,UPDATED_FILE=2,USE_ROUTER=4;
	public static function compile_for_file($file){
		$uri=preg_replace('/\/index\.(html?|php)$/','/',substr($file,strlen(ABSPATH)));
		if(($tmpl_file=self::get_tmpl_file_for_file($file)) || ($tmpl_file=self::get_tmpl_file_for_uri($uri))){
			ob_start();
			Page::init($uri);
			try{
				$site=Site::get_instance();
				$sitemap=$site->sitemap;
				$page=Page::get_instance();
				include $tmpl_file;
				if(!is_dir(dirname($file))){
					mkdir(dirname($file),0755,true);
				}
				file_put_contents($file,ob_get_clean());
				static::lint_file($file);
				usleep(1000);
			}
			catch(\Error $e){
				ob_end_clean();
				error_log($e->getMessage());
			}
			return self::UPDATED_FILE;
		}
		return self::attempt_routing($uri);
	}
	public static function lint_file($file){
		$site=Site::get_instance();
		switch(strrchr($file,'.')){
			case '.html':{
				if($site->runHtmlAsPhp){break;}
				$html=file_get_contents($file);
				$bf=new \MallardDuck\HtmlFormatter\Formatter();
				$config=$bf->getConfig();
				$config->set("tab","\t");
				$bf->setConfig($config);
				$html=$bf->beautify($html);
				file_put_contents($file,$html);
				break;
			}
		}
	}
	public static function attempt_routing($uri){
		if($router_file=self::get_router_file_for_uri($uri)){
			$router_uri=substr(dirname($router_file),strlen(ABSPATH)).'/*';
			if(!file_exists($f=dirname($router_file).'/.htaccess')){
				file_put_contents($f,"RewriteEngine on\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule . ".basename($router_file)." [L]");
			}
			if(($tmpl_file=self::get_tmpl_file_for_file($router_file)) || ($tmpl_file=self::get_tmpl_file_for_uri($router_uri))){
				ob_start();
				try{
					Page::init($router_uri);
					$site=Site::get_instance();
					$sitemap=$site->sitemap;
					$page=Page::get_instance();
					include $tmpl_file;
					if(!is_dir(dirname($router_file))){
						mkdir(dirname($router_file),0755,true);
					}
					file_put_contents($router_file,ob_get_clean());
					static::lint_file($router_file);
					usleep(1000);
					(function()use($router_file){include $router_file;})();
					return self::SHOULD_OUTPUT|self::UPDATED_FILE|self::USE_ROUTER;
				}
				catch(\Error $e){
					ob_end_clean();
					error_log($e->getMessage());
				}
			}
			(function()use($router_file){include $router_file;})();
			return self::SHOULD_OUTPUT|self::USE_ROUTER;
		}
		return 0;
	}
	public static function get_router_file_for_uri($uri){
		$site=Site::get_instance();
		$sitemap=$site->sitemap;
		if(substr($uri,0,1)!=='/'){return false;}
		if(
			substr($uri,-1)!=='/' &&
			file_exists(ABSPATH.$uri) ||
			file_exists(ABSPATH.$uri.'.tmpl.php') ||
			file_exists(TMPL_DIR.$uri.'.php')
		){return false;}
		$dir=substr($uri,-1)==='/'?substr($uri,0,-1):dirname($uri);
		while(($dir[0]??''==='/') && $dir!=='/'){
			if(isset($sitemap[$dir.'/*'])){
				foreach(['router.php','router.html','index.php','index.html'] as $file_name){
					if(
						file_exists(ABSPATH.$dir.'/'.$file_name) ||
						file_exists(ABSPATH.$dir.'/'.$file_name.'.tmpl.php') ||
						file_exists(TMPL_DIR.$dir.'/'.$file_name.'.php')
					){return ABSPATH.$dir.'/'.$file_name;}
				}
			}
			$dir=dirname($dir);
		}
		return false;
	}
	public static function get_tmpl_file_for_file($file){
		if(file_exists($f=$file.'.tmpl.php')){return $f;}
		$file_uri=substr($file,strlen(ABSPATH));
		if(file_exists($f=TMPL_DIR.$file_uri.'.php')){return $f;}
		if($f=static::get_tmpl_file_for_file_in_dir(ABSPATH,$file_uri,".tmpl.php")){return $f;}
		if($f=static::get_tmpl_file_for_file_in_dir(TMPL_DIR,$file_uri,".php")){return $f;}
		return false;
	}
	public static function get_tmpl_file_for_file_in_dir($f,$file_uri,$ext=''){
		$dnames=explode('/',substr($file_uri,1));
		$fname=array_pop($dnames);
		$fext=strrchr($fname,'.');
		$fbasename=preg_replace('/\.\w+$/','',$fname);
		$chunks=[];
		foreach($dnames as $dname){
			if(file_exists($tmp=$f.'/'.$dname)){$f=$tmp;continue;}
			if(file_exists($tmp=$f.'/[template]')){$chunks[]=$dname;$f=$tmp;continue;}
			return false;
		}
		if(!empty($chunks)){
			for($i=count($chunks)-1;$i>=0;$chunks[$i--]='*'){
				if(file_exists($tmp=$f.'/'.$fbasename.'['.implode('][',$chunks).']'.$fext.$ext)){return $tmp;}
			}
		}
		if(file_exists($tmp=$f.'/'.$fname.$ext)){return $tmp;}
		if(file_exists($tmp=$f.'/[template]'.$fext.$ext)){return $tmp;}
		return false;
	}
	public static function get_tmpl_file_for_uri($uri){
		$site=Site::get_instance();
		$sitemap=$site->sitemap;
		if(empty($sitemap[$uri]['template'])){return false;}
		if(file_exists($f=ABSPATH.$sitemap[$uri]['template'])){return $f;}
		if(file_exists($f=TMPL_DIR.$sitemap[$uri]['template'])){return $f;}
		return false;
	}
}