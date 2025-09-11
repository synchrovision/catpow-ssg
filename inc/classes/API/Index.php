<?php
namespace Catpow\API;
use Catpow\Site;
class Index{
	public static function request($req){
		$site=Site::get_instance();
		if(!empty($site->sitemap)){
			$uris=array_keys($site->sitemap);
			$results=[];
			foreach($uris as $uri){
				if(strpos($uri,'*')!==false){
					$chunks=explode('/*',$uri);
					$tmp=[[$chunks[0]]];
					foreach($chunks as $i=>$chunk){
						$tmp[$i+1]=[];
						foreach($tmp[$i] as $chunk_uri){
							if($index_file=Site::get_config_file_for_uri($chunk_uri,'index')){
								$index=(function($uri)use($index_file){return include $index_file;})($chunk_uri);
								foreach($index as $fname=>$info){
									$tmp[$i+1][]=$chunk_uri.'/'.$fname;
								}
							}
						}
					}
					foreach(end($tmp) as $result_uri){
						$results[$result_uri]=true;
					}
				}
				else{
					$results[$uri]=true;
				}
			}
			return array_keys($results);
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