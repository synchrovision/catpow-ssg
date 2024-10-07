<?php
namespace Catpow;
use Michelf\MarkdownExtra;
class MarkDown{
	public static function do_markdown($str){
		$parser=new MarkdownExtra;
		$parser->header_id_func=function($text){
			return preg_replace('/[^a-z0-9]/','-',strtolower($text));
		};
		ob_start();
		$str=$parser->transform($str);
		ob_end_clean();
		return $str;
	}
	public static function render($file){
		echo self::do_markdown(file_get_contents($file));
	}
}