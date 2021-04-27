<?php
namespace Catpow;
class ShortCode{
	public static $short_codes=[];
	public static function do_shortcode($content){
		$tags=array_keys(self::$short_codes);
		if(empty($tags) || strpos($content,'[')===false){return $content;}
		$reg='@\[(?P<tag>'.implode('|',$tags).')(\s(?P<attr>[^\]]+))?\]((?P<content>.+?)\[/\1\])?@s';
		return preg_replace_callback($reg,function($matches){
			return self::$short_codes[$matches['tag']](self::parse_attr_str($matches['attr']??''),$matches['content']??'');
		},$content);
	}
	public static function parse_attr_str($attr_str){
		if(empty($attr_str)){return [];}
		$attr_str=trim($attr_str);
		$attr=[];
		foreach(explode(' ',$attr_str) as $item){
			if(strpos($item,'=')){
				$item=explode('=',$item);
				$attr[$item[0]]=$item[1];
			}
			else{$attr[]=$item;}
		}
		return $attr;
	}
	public static function add_shortcode($tag,$function){
		self::$short_codes[$tag]=$function;
	}
}


?>