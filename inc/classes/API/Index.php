<?php
namespace Catpow\API;
class Index{
	public static function request($req){
		if(file_exists($f=CONF_DIR.'/site_config.php')){
			include $f;
			if(!empty($sitemap)){
				return array_keys($sitemap);
			}
		}
		return self::get_html_files_in_dir();
	}
	public static function get_html_files_in_dir($dir='/'){
		$files=[];
		foreach(glob(ABSPATH.$dir.'*.html') as $file){
			$files[]=$dir.basename($file);
		}
		foreach(glob(ABSPATH.$dir.'*.html.tmpl.php') as $tmpl_file){
			$file=substr($tmpl_file,0,-9);
			$files[]=$dir.basename($file);
		}
		foreach(glob(ABSPATH.'/_tmpl'.$dir.'*.html.php') as $tmpl_file){
			$file=substr(str_replace('/_tmpl/','/',$tmpl_file),0,-4);
			$files[]=$dir.basename($file);
		}
		foreach(glob(ABSPATH.$dir.'[!_]*',GLOB_ONLYDIR) as $childdir){
			$dname=basename($childdir);
			if($childfiles=self::get_html_files_in_dir($dir.$dname.'/')){
				$files=array_merge($files,$childfiles);
			}
		}
		foreach(glob(ABSPATH.'/_tmpl'.$dir.'[!_]*',GLOB_ONLYDIR) as $childdir){
			$dname=basename($childdir);
			if($childfiles=self::get_html_files_in_dir($dir.$dname.'/')){
				$files=array_merge($files,$childfiles);
			}
		}
		return array_values(array_unique($files));
	}
}