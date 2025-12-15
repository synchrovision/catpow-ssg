<?php
namespace Catpow;
class Jsx{
	public static function compile_for_file($file){
		if($source_file=self::get_source_file_for_file($file)){
			return self::bundle($source_file,$file);
		}
		if(($entry_file=self::get_entry_file_for_file($file))){
			return self::bundle($entry_file,$file);
		}
	}
	public static function init(){
		chdir(\INC_DIR);
		putenv('PATH='.getenv('PATH').':'.\INC_DIR.':'.\INC_DIR.'/node_modules/.bin');
		putenv('NODE_PATH='.getenv('NODE_PATH').':'.\INC_DIR.'/node_modules');
		if(!file_exists(\INC_DIR.'/node_modules')){passthru('npm update -i');}
	}
	public static function get_source_file_for_file($file){
		$uri=str_replace(\ABSPATH,'',preg_replace('/\.m?js$/','',$file));
		foreach(['tsx','ts','jsx'] as $ext){
			if(file_exists($f=\ABSPATH.$uri.'.'.$ext)){return $f;}
			if(file_exists($f=\ABSPATH.str_replace('/js/','/_'.$ext.'/',$uri).'.'.$ext)){return $f;}
			if(file_exists($f=\TMPL_DIR.$uri.'.'.$ext)){return $f;}
			if(file_exists($f=\TMPL_DIR.str_replace('/js/','/_'.$ext.'/',$uri).'.'.$ext)){return $f;}
		}
		foreach(['tsx','ts','jsx'] as $ext){
			if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,$uri.'.'.$ext)){return $f;}
			if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/js/','/_'.$ext.'/',$uri).'.'.$ext)){return $f;}
		}
		return false;
	}
	public static function get_entry_file_for_file($file){
		$uri=str_replace(\ABSPATH,'',preg_replace('/\.m?js$/','',$file));
		foreach(['tsx','ts','jsx','js'] as $ext){
			if(file_exists($f=\ABSPATH.$uri.'/index.'.$ext)){return $f;}
			if(file_exists($f=\ABSPATH.str_replace('/js/','/_'.$ext.'/',$uri).'/index.'.$ext)){return $f;}
			if(file_exists($f=\TMPL_DIR.$uri.'/index.'.$ext)){return $f;}
			if(file_exists($f=\TMPL_DIR.str_replace('/js/','/_'.$ext.'/',$uri).'/index.'.$ext)){return $f;}
		}
		foreach(['tsx','ts','jsx','js'] as $ext){
			if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,$uri.'/index.'.$ext)){return $f;}
			if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/js/','/_'.$ext.'/',$uri).'/index.'.$ext)){return $f;}
		}
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