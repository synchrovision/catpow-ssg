<?php
namespace Catpow\RXF;
abstract class date extends RXF{
	static $formats=[
		"/((?P<year>[\d]+)年)?((?P<month>[\d]+)月)?(?P<date>[\d]+)日(?P<day>[（\(][月火水木金土日祝][）\)])?/u"=>'callback:'
	];
	protected static function callback($matches,$class,$param){
		$text=sprintf('<span class="%s">',$class);
		if(!empty($matches['year'])){
			$text.=sprintf('<span class="%1$s"><span class="%1$s-num">%2$s</span><span class="%1$s-unit">年</span></span>',$class.'__year',$matches['year']);
		}
		if(!empty($matches['month'])){
			$text.=sprintf('<span class="%1$s"><span class="%1$s-num">%2$s</span><span class="%1$s-unit">月</span></span>',$class.'__month',$matches['month']);
		}
		$text.=sprintf('<span class="%1$s"><span class="%1$s-num">%2$s</span><span class="%1$s-unit">日</span></span>',$class.'__date',$matches['date']);
		if(!empty($matches['day'])){
			$text.=sprintf(
				'<span class="%1$s %2$s">%3$s</span>',
				$class.'__day',in_array($matches['day'],['日','祝'])?'is-holiday':'is-weekday',$matches['day']
			);
		}
		$text.='</span>';
		return $text;
	}
}