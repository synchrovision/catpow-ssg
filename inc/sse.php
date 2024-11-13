<?php
require_once __DIR__.'/settings.php';
header('Access-Control-Allow-Origin: '.\BASE_URL);
header("X-Accel-Buffering: no");
header("Content-Type: text/event-stream");
header("Cache-Control: no-cache");
header('Connection: keep-alive');

init();

$mtimes=extract_mtimes('.');

while(true){
	if(!empty($updated_files=check_update($mtimes))){
		printf("event:%s\ndata:%s\n\n",'update',json_encode($updated_files,0500));
		if(ob_get_contents()){ob_end_flush();}
		flush();
	}
	else{
		printf("event:%s\ndata:%s\n\n",'ping',json_encode(['time'=>time()],0500));
	}
	if(connection_aborted()){break;}
	sleep(2);
}


function init(){
	set_time_limit(0);
	chdir(ABSPATH);
	require_once INC_DIR.'/vendor/autoload.php';
	if(file_exists(APP_DIR.'/.env')){
		$dotenv = Dotenv\Dotenv::createImmutable(\APP_DIR);
		$dotenv->load();
	}
	if(file_exists($f=CONF_DIR.'/functions.php')){require_once($f);}
	if(file_exists($f=INC_DIR.'/functions.php')){require_once($f);}
	if(file_exists($f=CONF_DIR.'/init.php')){require_once($f);}
}

function extract_mtimes($dir){
	$files=[];
	foreach(scandir($dir) as $fname){
		if(in_array($fname,['.','..','_compiler','_notes','_uploader','node_modules'],true)){continue;}
		if($fname==='vendor' && file_exists($dir.'/vendor/autoload.php')){continue;}
		$f=$dir.'/'.$fname;
		if(is_dir($f)){
			$files=array_merge($files,extract_mtimes($f));
		}
		else if(in_array(strrchr($fname,'.'),['.html','.php','.csv','.css','.scss','.js','.ts','.jsx','.tsx','.json','.jpeg','.jpg','.png','.gif','.webp'],true)){
			$files[$f]=filemtime($f);
		}
	}
	return $files;
}
function check_update(&$mtimes){
	$updated_files=[];
	foreach($mtimes as $file=>$mtime){
		clearstatcache($file);
		if($mtime!==filemtime($file)){
			$updated_files[$file]=$mtimes[$file]=filemtime($file);
		}
	}
	return $updated_files;
}