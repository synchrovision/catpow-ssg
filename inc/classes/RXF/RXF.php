<?php
namespace Catpow\RXF;
abstract class RXF{
	public static $default_class_prefix='rxf';
	static $formats,$regsitered=null;
	protected static function callback($matches,$class,$param){
		return sprintf('<span class="%s">%s</span>',$class,$matches[0]);
	}
	public static function replace($text,$pref=null){
		if(strpos($text,'{')===false){return $text;}
		if(empty($pref)){$pref=self::$default_class_prefix;}
		return preg_replace_callback('/{(.+?)}/',function($matches)use($pref){
			foreach(self::get_regsitered() as $name){
				$className="\\Catpow\\RXF\\{$name}";
				foreach($className::$formats as $pattern=>$format){
					if(preg_match($pattern,$matches[1],$formatMatches)){
						if(substr($format,0,9)==='callback:'){
							return $className::callback($formatMatches,$pref.'-'.$name,substr($format,9));
						}
						return str_replace(
							['{$pref}','{$name}','{$class}'],
							[$pref,$name,$pref.'-'.$name],
							preg_replace($pattern,$format,$matches[1])
						);
					}
				}
			}
			return $matches[0];
		},$text);
	}
	private static function get_regsitered(){
		if(isset(static::$regsitered)){return static::$regsitered;}
		$flags=[];
		foreach([\ABSPATH,\TMPL_DIR,\INC_DIR] as $dir){
			if(is_dir($d=$dir.'/classes/RXF')){
				foreach(scandir($d) as $fname){
					if(substr($fname,-4)==='.php'){
						$flags[substr($fname,0,-4)]=1;
					}
				}
			}
		}
		unset($flags['RXF']);
		return static::$regsitered=array_keys($flags);
	}
}