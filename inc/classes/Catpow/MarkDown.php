<?php
namespace Catpow;
class MarkDown{
	public static function do_markdown($str){
		return \Michelf\MarkdownExtra::defaultTransform($str);
	}
	public static function render($file){
		echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents($file));
	}
}
?>