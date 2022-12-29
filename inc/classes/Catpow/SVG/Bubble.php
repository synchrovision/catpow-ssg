<?php
namespace Catpow\SVG;
class Bubble extends Shape{
	protected $default_atts=['fill'=>'currentColor'];
	public function render(){
		srand($this->props['seed']??1);
		$num=$this->props['num']??rand(10,100);
		$x=$this->props['x']??$this->container->x;
		$y=$this->props['y']??$this->container->y;
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		$u=ceil(sqrt($width*$height/$num));
		$min=$this->props['min']??rand(1,$u>>3);
		$max=$this->props['max']??rand($u>>3,$u);
		$d='';
		for($i=0;$i<$num;$i++){
			$r=rand($min,$max);
			$r2=$r*2;
			$x0=$x+rand(0,$width);
			$y0=$y+rand(0,$height)-$r;
			$d.="M {$x0},{$y0} a {$r} {$r} 0 1 1 0 {$r2} a {$r} {$r} 0 1 1 0 -{$r2} ";
		}
		printf('<path class="%s" d="%s"%s/>',$this->className,$d,$this->get_attributes());
	}
}