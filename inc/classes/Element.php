<?php
namespace Catpow;
class Element{
	static $all_elements;
	public static function compile($element){
		$js_file=ABSPATH.'/elements/script.js';
		$jsx_file=self::get_element_file($element,'script.jsx');
		$css_file=self::get_element_file($element,'style.css');
		$scss_file=self::get_element_file($element,'style.scss');
		$max_mtime=filemtime($jsx_file);
		if($scss_file){
			if(empty($css_file) || filemtime($css_file)<filemtime($scss_file)){
				if(empty($css_file)){
					$css_file=substr($scss_file,0,-4).'css';
				}
				Scss::compile($scss_file,$css_file);
			}
			$max_mtime=max($max_mtime,filemtime($css_file));
		}
		if(!file_exists($js_file) || filemtime($js_file)<$max_mtime){
			Jsx::bundle($jsx_file,$js_file);
		}
	}
	public static function get_element_file($element,$file){
		if(file_exists($f=ABSPATH.'/elements/'.$element.'/'.$file)){return $f;}
		if(file_exists($f=TMPL_DIR.'/elements/'.$element.'/'.$file)){return $f;}
		if(file_exists($f=INC_DIR.'/elements/'.$element.'/'.$file)){return $f;}
		return false;
	}
	public static function enqueue_elements_in_html($html){
		global $page;
		foreach(self::get_all_elements() as $element){
			if(strpos($html,'<'.$element)!==false){
				self::compile($element);
				$page->use_element($element);
			}
		}
		return $html;
	}
	public static function get_all_elements(){
		if(isset(static::$all_elements)){return static::$all_elements;}
		$flags=[];
		foreach([ABSPATH,TMPL_DIR,INC_DIR] as $dir){
			if(!is_dir($dir.'/elements')){continue;}
			foreach(scandir($dir.'/elements') as $f){
				if(preg_match('/^\w+\-\w+/',$f)){$flags[$f]=1;}
			}
		}
		return static::$all_elements=array_keys($flags);
	}
}