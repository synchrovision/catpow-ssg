<?php
namespace Catpow;
class Jsx{
	public static function compile_for_file($file){
		if($jsx_file=self::get_jsx_file_for_file($file)){
			return self::bundle($jsx_file,$file);
		}
		if(($entry_file=self::get_entry_jsx_file_for_file($file)) || ($entry_file=self::get_entry_tsx_file_for_file($file))){
			return self::bundle($entry_file,$file);
		}
	}
	public static function init(){
		chdir(\INC_DIR);
		putenv('PATH='.getenv('PATH').':'.\INC_DIR.':'.\INC_DIR.'/node_modules/.bin');
		putenv('NODE_PATH='.getenv('NODE_PATH').':'.\INC_DIR.'/node_modules');
		if(!file_exists(\INC_DIR.'/node_modules')){passthru('npm update -i');}
	}
	public static function get_jsx_file_for_file($file){
		$jsx_file=substr($file,0,-2).'jsx';
		if(file_exists($jsx_file)){return $jsx_file;}
		if(file_exists($f=str_replace('/js/','/_jsx/',$jsx_file))){return $f;}
		$jsx_file_uri=str_replace(\ABSPATH,'',$jsx_file);
		if(file_exists($f=\TMPL_DIR.$jsx_file_uri)){return $f;}
		if(file_exists($f=\TMPL_DIR.str_replace('/js/','/_jsx/',$jsx_file_uri))){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,$jsx_file_uri)){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/js/','/_jsx/',$jsx_file_uri))){return $f;}
		return false;
	}
	public static function get_entry_jsx_file_for_file($file){
		$entry_jsx_file=substr($file,0,-3).'/index.jsx';
		if(file_exists($entry_jsx_file)){return $entry_jsx_file;}
		if(file_exists($f=str_replace('/js/','/_jsx/',$entry_jsx_file))){return $f;}
		$jsx_file_uri=str_replace(\ABSPATH,'',$entry_jsx_file);
		if(file_exists($f=\TMPL_DIR.$jsx_file_uri)){return $f;}
		if(file_exists($f=\TMPL_DIR.str_replace('/js/','/_jsx/',$jsx_file_uri))){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,$jsx_file_uri)){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/js/','/_jsx/',$jsx_file_uri))){return $f;}
		return false;
	}
	public static function get_entry_tsx_file_for_file($file){
		$entry_tsx_file=substr($file,0,-3).'/index.tsx';
		if(file_exists($entry_tsx_file)){return $entry_tsx_file;}
		if(file_exists($f=str_replace('/js/','/_tsx/',$entry_tsx_file))){return $f;}
		$jsx_file_uri=str_replace(\ABSPATH,'',$entry_tsx_file);
		if(file_exists($f=\TMPL_DIR.$jsx_file_uri)){return $f;}
		if(file_exists($f=\TMPL_DIR.str_replace('/js/','/_jsx/',$jsx_file_uri))){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,$jsx_file_uri)){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/js/','/_jsx/',$jsx_file_uri))){return $f;}
		return false;
	}
	public static function bundle($entry_file,$bundle_js_file){
		self::init();
		if(!file_exists($entry_file)){return;}
		$latest_filetime=filemtime($entry_file);
		foreach(glob(dirname($entry_file).'/*') as $bundle_file){
			$latest_filetime=max($latest_filetime,filemtime($bundle_file));
		}
		if(!file_exists($bundle_js_file) or filemtime($bundle_js_file) < $latest_filetime){
			$site=Site::get_instance();
			$command="node bundle.esbuild.mjs {$entry_file} {$bundle_js_file}";
			if($site->useGlobalReact){$command.=' --useGlobalReact';}
			if($site->debugMode){$command.=' --debugMode';}
			ob_start();
			passthru($command);
			error_log(ob_get_clean());
		}
	}
}