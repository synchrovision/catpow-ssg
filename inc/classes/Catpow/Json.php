<?php
namespace Catpow;

class Json{
	public static function compile_for_file($file){
		if($tmpl_file=Tmpl::get_tmpl_file_for_file($file)){
			ob_start();
			try{
				$data=include $tmpl_file;
				if(!is_dir(dirname($file))){
					mkdir(dirname($file),0755,true);
				}
				if(empty($data)){
					file_put_contents($file,ob_get_clean());
				}
				else{
					file_put_contents($file,json_encode($data,0700));
					ob_end_clean();
				}
				usleep(1000);
			}
			catch(\Error $e){
				ob_end_clean();
				error_log($e->getMessage());
			}
			return Tmpl::UPDATED_FILE;
		}
	}
}