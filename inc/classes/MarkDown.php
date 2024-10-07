<?php
namespace Catpow;
use Michelf\MarkdownExtra;
class MarkDown{
	public static function do_markdown($str){
		if(strpos($str,'```')!==false){
			enqueue_script('prism-core','https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js');
			enqueue_script('prism-autoloader','https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js');
			enqueue_style('prism','https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism.min.css');
		}
		$parser=new MarkdownExtra;
		$parser->header_id_func=function($text){
			return preg_replace('/[\s]/','-',strtolower($text));
		};
		$parser->code_class_prefix='lang-';
		ob_start();
		$str=$parser->transform($str);
		ob_end_clean();
		return $str;
	}
	public static function render($file){
		echo self::do_markdown(file_get_contents($file));
	}
}