<?php
ini_set("error_log","php://stdout");
define('ABSPATH',dirname(__DIR__));
define('BASE_URL','http://localhost:8000');
define('APP_DIR',__DIR__);
define('APP_NAME',basename(__DIR__));
define('APP_URL',BASE_URL.'/'.APP_NAME);
define('CP_DIR',APP_DIR.'/controlpanel');
define('CP_URL',APP_URL.'/controlpanel');
if(php_sapi_name()==='cli'){
	chdir(APP_DIR);
	passthru('git submodule update --init --recursive');
	chdir(ABSPATH);
	passthru('php -S localhost:8000 '.APP_NAME.'/server.php & open '.CP_URL.'/');
	return;
}
define('API_URL',APP_URL.'/api');
define('INC_DIR',APP_DIR.'/inc');
define('CONF_DIR',ABSPATH.'/_config');
define('TMPL_DIR',ABSPATH.'/_tmpl');

$uri=explode('?',$_SERVER["REQUEST_URI"])[0];
if(strpos($uri,'/'.APP_NAME.'/api/')===0){
	init();
	Catpow\API::request(substr($uri,strpos($uri,'/api/')+5),$_REQUEST);
	return;
}
if(substr($uri,-1)==='/'){
	if(strpos($uri,'/'.APP_NAME.'/controlpanel/')===0){
		include CP_DIR.'/index.php';
		return;
	}
	$file=ABSPATH.$uri.'index.html';
	$fname='index.html';
}
else{
	$file=ABSPATH.$uri;
	$fname=basename($uri);
}
define('PAGE_DIR',dirname($file));
define('PAGE_TMPL_DIR',str_replace(ABSPATH,TMPL_DIR,PAGE_DIR));
switch(substr($fname,strrpos($fname,'.')+1)){
	case 'js':
		init();
		Catpow\Jsx::compile_for_file($file);
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
	case 'json':
		if(file_exists($tmpl_file=str_replace(ABSPATH,TMPL_DIR,$file)) || file_exists($tmpl_file=str_replace(ABSPATH,INC_DIR,$file))){
			if(!file_exists($file) || filemtime($file)<filemtime($tmpl_file)){
				if(!is_dir(dirname($file))){
					mkdir(dirname($file),0755,true);
				}
				copy($tmpl_file,$file);
			}
		}
		return false;
	case 'css':
		init();
		Catpow\Scss::compile_for_file($file);
		return false;
	case 'html':
	case 'svg':
	case 'rss':
	case 'rdf':
	case 'xml':
		init();
		if(file_exists($site_config_file=CONF_DIR.'/site_config.php')){
			global $sitemap;
			include($site_config_file);
			Catpow\Site::init($site??null);
			foreach(['site','sitemap'] as $var_name){
				if(empty($$var_name)){continue;}
				if(!is_dir(TMPL_DIR."/json")){mkdir(TMPL_DIR."/json",0755,true);}
				$json_file=TMPL_DIR."/json/{$var_name}.json";
				if(!file_exists($json_file) || filemtime($json_file)<filemtime($site_config_file)){
					file_put_contents($json_file,str_replace(['"TRUE"','"FALSE"'],['true','false'],json_encode($$var_name,0700)));
				} 
			}
		}
		$result=Catpow\Tmpl::compile_for_file($file);
		$should_output=!empty($result&Catpow\Tmpl::SHOULD_OUTPUT);
		if(substr($file,-5)==='.html'){
			$contents=file_get_contents(($result&Catpow\Tmpl::USE_ROUTER)?(Catpow\Tmpl::get_router_file_for_uri($uri)):$file);
			if(strpos($contents,'<!--#include ')){
				echo preg_replace_callback('/<\!\-\-#include (virtual|file)="(.+?)" \-\->/',function($matches){
					switch($matches[1]){
						case 'virtual':return file_get_contents(ABSPATH.$matches[2]);
						case 'file':return file_get_contents(PAGE_DIR.'/'.$matches[2]);
					}
				},$contents);
				$should_output=true;
			}
		}
		return $should_output;
	default:
		return false;
}

function init(){
	require_once INC_DIR.'/vendor/autoload.php';
	spl_autoload_register(function($class){
		if(file_exists($f=CONF_DIR.'/classes/'.str_replace('\\','/',$class).'.php')){include($f);return;}
		if(file_exists($f=INC_DIR.'/classes/'.str_replace('\\','/',$class).'.php')){include($f);}
	});
	if(file_exists(APP_DIR.'/.env')){
		$dotenv = Dotenv\Dotenv::createImmutable(APP_DIR);
		$dotenv->load();
	}
	if(file_exists($f=CONF_DIR.'/functions.php')){include($f);}
	if(file_exists($f=INC_DIR.'/functions.php')){include($f);}
}