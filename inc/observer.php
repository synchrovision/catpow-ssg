<?php
namespace Catpow;
require_once __DIR__.'/settings.php';
header('Access-Control-Allow-Origin: '.\BASE_URL);
header('Access-Control-Allow-Headers: Content-Type');


$deps=json_decode(file_get_contents('php://input'),true);

if(empty($deps['html'])){
	sleep(5);
	die('{"updated":false}');
}

init();
$target_files=get_target_files($deps);
$mtimes=get_mtimes($target_files);


for($i=0;$i<10;$i++){
	if(is_modified($mtimes)){
		echo '{"updated":true}';
		exit;
	}
	sleep(2);
}
echo '{"updated":false}';


function get_target_files($deps){
	if(empty($deps['html'])){return [];}
	$files=[];
	$html=\ABSPATH.$deps['html'];
	if(substr($html,-1)==='/'){$html.='index.html';}
	if(($tmpl=Tmpl::get_tmpl_file_for_file($html)) || ($tmpl=Tmpl::get_tmpl_file_for_uri($deps['html']))){
		$files[]=$tmpl;
	}
	else{
		$files[]=$html;
	}
	foreach($deps['js'] as $js){
		$js=\ABSPATH.$js;
		if($tmpl=Jsx::get_jsx_file_for_file($js)){
			$files[]=$tmpl;
		}
		elseif(($tmpl=Jsx::get_entry_jsx_file_for_file($js)) || ($tmpl=Jsx::get_entry_tsx_file_for_file($js))){
			$files[]=$tmpl;
			$files=array_merge($files,glob(dirname($tmpl).'/{,*/,*/*/}*.{js,jsx,ts}',GLOB_BRACE));
		}
		else{
			$files[]=$js;
		}
	}
	if(file_exists($style_config=\CONF_DIR.'/style_config.scss')){
		$files[]=$style_config;
	}
	foreach($deps['css'] as $css){
		$css=\ABSPATH.$css;
		if($scss=Scss::get_scss_file_for_file($css)){
			$files[]=$scss;
			$files=array_merge($files,glob(dirname($scss).'/{,*/,*/*/}*.scss',GLOB_BRACE));
		}
		else{
			$files[]=$css;
		}
	}
	return $files;
}
function get_mtimes($files){
	$mtimes=[];
	foreach($files as $file){
		$mtimes[$file]=filemtime($file);
	}
	return $mtimes;
}
function is_modified($mtimes){
	foreach($mtimes as $file=>$mtime){
		clearstatcache($file);
		if(filemtime($file)!==$mtime){
			return true;
		}
	}
	return false;
}
function init(){
	set_time_limit(0);
	require_once \INC_DIR.'/vendor/autoload.php';
	if(file_exists(\APP_DIR.'/.env')){
		$dotenv = \Dotenv\Dotenv::createImmutable(\APP_DIR);
		$dotenv->load();
	}
	if(file_exists($f=\CONF_DIR.'/functions.php')){require_once($f);}
	if(file_exists($f=\INC_DIR.'/functions.php')){require_once($f);}
	if(file_exists($f=\CONF_DIR.'/init.php')){require_once($f);}
}