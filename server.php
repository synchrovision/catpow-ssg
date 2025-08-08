<?php
if(file_exists($settings_file=dirname(__DIR__).'/_config/settings.php')){
	include $settings_file;
}
require 'inc/settings.php';
if(php_sapi_name()==='cli'){
	chdir(APP_DIR);
	passthru('git submodule update --init --recursive');
	chdir(ABSPATH);
	$descriptor=[['pipe','r'],['file','php://stdout','w'],['file','php://stdout','w']];
	$main_proc=proc_open(sprintf('php -S %s %s/server.php -t %s/ & open %s/',BASE_HOST,APP_DIR,ABSPATH,CP_URL),$descriptor,$pipes);
	$sub_proc=proc_open(sprintf('php -S %s %s/inc/sse.php',SSE_HOST,APP_DIR),$descriptor,$pipes);
	while(!feof(STDIN)){sleep(10);}
	proc_close($main_proc);
	proc_close($sub_proc);
	return;
}

$uri=explode('?',$_SERVER["REQUEST_URI"])[0];

if(strpos($uri,'/'.APP_NAME.'/')===0){
	if(strpos($uri,'/'.APP_NAME.'/api/')===0){
		init();
		Catpow\API::request(substr($uri,strpos($uri,'/api/')+5),$_REQUEST);
		return;
	}
	if($uri==='/'.APP_NAME.'/controlpanel/'){
		include CP_DIR.'/index.php';
		return;
	}
	if(file_exists($file=dirname(APP_DIR).$uri)){
		$mime=mime_content_type($file);
		if($mime==='text/plain'){
			$mime=[
				'.js'=>'text/javascript',
				'.json'=>'application/json',
				'.css'=>'text/css',
			][strrchr($file,'.')]??$mime;
		}
		header('Content-Type:'.$mime);
		readfile($file);
	}
	return;
}
if(substr($uri,-1)==='/'){
	$file=ABSPATH.$uri.'index.html';
	$fname='index.html';
}
else{
	$file=ABSPATH.$uri;
	$fname=basename($uri);
}
define('PAGE_DIR',dirname($file));
define('PAGE_TMPL_DIR',str_replace(ABSPATH,TMPL_DIR,PAGE_DIR));
switch($ext=substr($fname,strrpos($fname,'.')+1)){
	case 'js':
	case 'json':
		init();
		['js'=>'Catpow\\Jsx','json'=>'Catpow\\Json'][$ext]::compile_for_file($file);
	case 'png':
	case 'jpg':
	case 'jpeg':
	case 'gif':
	case 'eot':
	case 'woff':
	case 'woff2':
	case 'ttf':
	case 'otf':
	case 'pdf':
	case 'mp3':
	case 'mp4':
		if(file_exists($tmpl_file=str_replace(ABSPATH,TMPL_DIR,$file)) || file_exists($tmpl_file=str_replace(ABSPATH,INC_DIR,$file))){
			if(!file_exists($file) || filemtime($file)<filemtime($tmpl_file)){
				if(!is_dir(dirname($file))){
					mkdir(dirname($file),0755,true);
				}
				copy($tmpl_file,$file);
			}
		}
		else{
			init();
			if($tmpl_file=Catpow\Tmpl::get_tmpl_file_for_file_in_dir(TMPL_DIR,$uri)){
				if(!file_exists($file) || filemtime($file)<filemtime($tmpl_file)){
					if(!is_dir(dirname($file))){
						mkdir(dirname($file),0755,true);
					}
					copy($tmpl_file,$file);
				}
			}
			if(!file_exists($file)){
				$result=Catpow\Tmpl::attempt_routing($uri);
				if($result!==0){return $result;}
				Catpow\Site::copy_file_from_remote_if_not_exists($uri);
			}
		}
		return false;
	case 'css':
		init();
		Catpow\Scss::compile_for_file($file);
		$result=Catpow\Tmpl::attempt_routing($uri);
		if($result!==0){return $result;}
		Catpow\Site::copy_file_from_template_if_not_exists_or_updated($uri);
		Catpow\Site::copy_file_from_remote_if_not_exists($uri);
		return false;
	case 'html':
	case 'shtml':
	case 'svg':
	case 'rss':
	case 'rdf':
	case 'xml':
		init();
		$result=Catpow\Tmpl::compile_for_file($file);
		$should_output=!empty($result&Catpow\Tmpl::SHOULD_OUTPUT);
		Catpow\Site::copy_file_from_template_if_not_exists_or_updated($uri);
		if(substr($file,-5)==='.html' || substr($file,-6)==='.shtml'){
			$contents=file_get_contents(($result&Catpow\Tmpl::USE_ROUTER)?(Catpow\Tmpl::get_router_file_for_uri($uri)):$file);
			if(strpos($contents,'<!--#include ')){
				echo preg_replace_callback('/<\!\-\-#include (virtual|file)="(.+?)"\s*\-\->/',function($matches){
					switch($matches[1]){
						case 'virtual':return file_get_contents(ABSPATH.$matches[2]);
						case 'file':return file_get_contents(PAGE_DIR.'/'.$matches[2]);
					}
				},$contents);
				$should_output=true;
			}
		}
		if(!file_exists($file)){
			Catpow\Site::copy_file_from_remote_if_not_exists($uri);
		}
		
		if($ext==='html'){
			$site=Catpow\Site::get_instance();
			if($site->runHtmlAsPhp){
				include $file;
				return true;
			}
		}
		return $should_output;
	default:
		if(!file_exists($file)){
			init();
			$result=Catpow\Tmpl::attempt_routing($uri);
			if($result!==0){return $result;}
			Catpow\Site::copy_file_from_remote_if_not_exists($uri);
		}
		return false;
}

function init(){
	require_once INC_DIR.'/vendor/autoload.php';
	if(file_exists(APP_DIR.'/.env')){
		$dotenv = Dotenv\Dotenv::createImmutable(APP_DIR);
		$dotenv->load();
	}
	if(file_exists($f=CONF_DIR.'/functions.php')){require_once($f);}
	if(file_exists($f=INC_DIR.'/functions.php')){require_once($f);}
	if(file_exists($f=CONF_DIR.'/init.php')){require_once($f);}
}