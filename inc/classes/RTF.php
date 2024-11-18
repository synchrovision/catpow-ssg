<?php
namespace Catpow;

class RTF{
	public static function replace($text,$pref){
		$text=self::replace_block_format($text,$pref);
		$text=self::join_consective_lists($text,$pref);
		$text=RXF\RXF::replace($text,$pref);
		$text=self::replace_inline_format($text,$pref);
		$text=self::replace_linebreak($text);
		return $text;
	}
	private static function replace_inline_format($text,$pref){
		$text=preg_replace('/\(\((.+?)\)\)/u','<small class="'.$pref.'-small">$1</small>',$text);
		$text=preg_replace('/\*\*\*\*(.+?)\*\*\*\*/u','<strong class="'.$pref.'-strongest">$1</strong>',$text);
		$text=preg_replace('/\*\*\*(.+?)\*\*\*/u','<strong class="'.$pref.'-stronger">$1</strong>',$text);
		$text=preg_replace('/\*\*(.+?)\*\*/u','<strong class="'.$pref.'-strong">$1</strong>',$text);
		$text=preg_replace('/##(.+?)##/u','<em class="'.$pref.'-em">$1</em>',$text);
		$text=preg_replace('/~~(.+?)~~/u','<del class="'.$pref.'-del">$1</del>',$text);
		$text=preg_replace('/``(.+?)``/u','<code class="'.$pref.'-code">$1</code>',$text);
		$text=preg_replace('/!\[(.+?)\]\((.+?)\)/u','<img class="'.$pref.'-image" src="$2" alt="$1"/>',$text);
		$text=preg_replace('/\[tel:((\d+)\-(\d+)\-(\d+))\]/u','<a class="'.$pref.'-tel" href="tel:$2$3$4" target="_brank">$1</a>',$text);
		$text=preg_replace('/\[mail:(.+?@.+?)\]/u','<a class="'.$pref.'-mailto" href="mailto:$1" target="_brank">$1</a>',$text);
		$text=preg_replace('/\[\[(.+?)\]\]\((.+?)\)/u','<a class="'.$pref.'-button" href="$2" target="_brank"><span class="'.$pref.'-button__label">$1</span></a>',$text);
		$text=preg_replace('/\[(https?:\/\/.+?)\]\((.+?)\)/u','<a class="'.$pref.'-link is-link-external" href="$2" target="_brank">$1</a>',$text);
		$text=preg_replace('/\[(.+?)\]\((.+?)\)/u','<a class="'.$pref.'-link" href="$2" target="_brank">$1</a>',$text);
		return $text;
	}
	private static function replace_block_format($text,$pref,$level=0){
		$h='/^'.($level>0?"([　\\t]{{$level}})":'()');
		$t='(.+((\\n'.($level>0?'\\1':'').'[　\\t]).+)*)$/um';
		$c=$level>0?" is-level-{$level}":'';
		$l=$level+4;
		$p="$2\n";
		$p2="$3\n";
		if($level>0 && !preg_match($h.'/um',$text)){return $text;}
		$text=preg_replace(
			$h.'\^([^\s　].{0,8}?) [:：] '.$t,
			'<dl class="'.$pref.'-notes'.$c.'"><dt class="'.$pref.'-notes__dt">$2</dt><dd class="'.$pref.'-notes__dd">'.$p2.'</dd></dl><!--/notes-->',
			$text
		);
		$text=preg_replace(
			$h.'([^\s　].{0,8}?) [:：] '.$t,
			'<dl class="'.$pref.'-dl'.$c.'"><dt class="'.$pref.'-dl__dt">$2</dt><dd class="'.$pref.'-dl__dd">'.$p2.'</dd></dl>',
			$text
		);
		$text=preg_replace($h.'※'.$t,'<span class="'.$pref.'-annotation'.$c.'">'.$p.'</span>',$text);
		$text=preg_replace($h.'■ '.$t,'<h'.$l.' class="'.$pref.'-title'.$c.'">'.$p.'</h'.$l.'>',$text);
		$text=preg_replace($h.'・ '.$t,'<ul class="'.$pref.'-ul'.$c.'"><li class="'.$pref.'-ul__li">'.$p.'</li></ul>',$text);
		$text=preg_replace($h.'\d{1,2}\. '.$t,'<ol class="'.$pref.'-ol'.$c.'"><li class="'.$pref.'-ol__li">'.$p.'</li></ol>',$text);
		$text=preg_replace(
			$h.'([①-⑳]|[^\s　]\.) '.$t,
			'<dl class="'.$pref.'-listed'.$c.'"><dt class="'.$pref.'-listed__dt">$2</dt><dd class="'.$pref.'-listed__dd">'.$p2.'</dd></dl><!--/listed-->',
			$text
		);
		if($level<3){return self::replace_block_format($text,$pref,$level+1);}
		return $text;
	}
	private static function join_consective_lists($text,$pref){
		$text=preg_replace('/<\/(dl|ul|ol)>\s*<\1 class="'.$pref.'\-\1.*?">/u','',$text);
		$text=preg_replace('/<\/(dl|ul|ol)><!\-\-\/(\w+?)\-\->\s*<\1 class="'.$pref.'\-\2.*?">/u','',$text);
		$text=preg_replace('/(<\/(dl|ul|ol)>)<!\-\-\/\w+?\-\->/u','$1',$text);
		return $text;
	}
	private static function replace_linebreak($text){
		$text=preg_replace('/\s*(<\/(h\d|dl|dt|dd|ul|ol|li)+?>)\s*/um','$1',$text);
		$text=preg_replace('/(  \n[　\t]*|\n[　\t]+)/um','<br/>',$text);
		return $text;
	}
	
}