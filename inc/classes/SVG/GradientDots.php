<?php
namespace Catpow\SVG;
class GradientDots extends Shape{
	protected $default_atts=['fill'=>'currentColor'];
	public function render(){
		srand($this->props['seed']??1);
		$r=$this->props['r']??rand(6,60);
		$u=$this->props['u']??rand($r<<2,$r*2);
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		$d='';
		for($y=-$r;$y<$height;$y+=$u){
			for($i=0;$i<2;$i++){
				$y0=$y+$u*$i/2;
				$r1=$r-$r*$y0/$height;
				if($r1<1){break;}
				$r2=$r1*2;
				for($x=-$i*$u/2;$x<$width+$r;$x+=$u){
					$d.="M {$x},{$y0} a {$r1} {$r1} 0 1 1 0 {$r2} a {$r1} {$r1} 0 1 1 0 -{$r2} ";
				}
			}
		}
		printf('<path class="%s" d="%s"%s/>',$this->className,$d,$this->get_attributes());
	}
}