<?php
namespace Catpow;
class Scss{
	public static function compile_for_file($file){
		if($scss_file=self::get_scss_file_for_file($file)){
			self::compile($scss_file,$file);
		}
	}
	public static function get_scss_file_for_file($file){
		$scss_file=substr($file,0,-3).'scss';
		if(file_exists($scss_file)){return $scss_file;}
		if(file_exists($f=str_replace('/css/','/_scss/',$scss_file))){return $f;}
		if(file_exists($f=str_replace(ABSPATH,TMPL_DIR,$scss_file))){return $f;}
		if(file_exists($f=str_replace([ABSPATH,'/css/'],[TMPL_DIR,'/_scss/'],$scss_file))){return $f;}
		return false;
	}
	public static function compile($scss_file,$css_file){
		if(version_compare(PHP_VERSION, '5.4')<0)return;
		static $scssc;
		if(
			file_exists($config_file=ABSPATH.'/_scss/style_config.scss') ||
			file_exists($config_file=CONF_DIR.'/style_config.scss')
		){
			$style_config_modified_time=filemtime($config_file);
		}
		else{
			$style_config_modified_time=0;
		}
		if(!is_dir(dirname($css_file))){mkdir(dirname($css_file),0777,true);}
		if(
			!file_exists($css_file) or
			filemtime($css_file) < max(
				filemtime($scss_file),
				$style_config_modified_time
			)
		){
			if(empty($scssc)){
				$scssc = new \ScssPhp\ScssPhp\Compiler();
				$scssc->addImportPath(ABSPATH.'/');
				$scssc->addImportPath(CONF_DIR.'/');
				$scssc->addImportPath(ABSPATH.'/_scss/');
				$scssc->addImportPath(INC_DIR.'/scss/');
				$scssc->setSourceMap(\ScssPhp\ScssPhp\Compiler::SOURCE_MAP_FILE);
				$scssc->setIgnoreErrors(true);
			}
			try{
				$scssc->setSourceMapOptions([
					'sourceMapWriteTo'=>$css_file.'.map',
					'sourceMapURL'=>'./'.basename($css_file).'.map',
					'sourceMapFilename'=>basename($css_file).'.map',
					'sourceMapBasepath'=>$_SERVER['DOCUMENT_ROOT'],
					'sourceRoot'=>'/'
				]);
				$css=$scssc->compile(file_get_contents($scss_file),$scss_file);
			}catch(Exception $e){
				echo $e->getMessage();
			}
			file_put_contents($css_file,$css);
		}
	}
}