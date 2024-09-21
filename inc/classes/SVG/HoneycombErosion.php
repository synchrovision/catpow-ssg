<?php
namespace Catpow\SVG;
use Catpow\Scss;
use Catpow\Calc;
class HoneycombErosion extends Erosion{
	protected $default_atts=['fill'=>'currentColor'];
	public function render(){
		srand($this->props['seed']??1);
		$x=$this->props['x']??$this->container->x;
		$y=$this->props['y']??$this->container->y;
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		$r=$this->props['r']??rand(1,5)*20;
		$p=$this->props['p']??rand(1,15);
		$m=$this->props['m']??pow(rand(0,16),2);
		$ux=$r*M_SQRT3/2;
		$uy=$r*0.75;
		$w=ceil($width/$ux/2)+1;
		$h=ceil($height/$uy/2)+1;
		$d='';
		foreach(self::get_cell_map($w,$h,$p,$m) as $cell){
			$d.=sprintf(
				'M %d,%d l %3$d,%4$d 0,%5$d -%3$d,%4$d -%3$d,-%4$d 0,-%5$d z ',
				$cell[0]*$ux*2-$ux*($cell[1]%2),
				$cell[1]*$uy*2-$r,
				$ux-1,$r/2-1,$r-1
			);
		}
		printf('<path class="%s" d="%s"%s/>',$this->className,$d,$this->get_attributes());
	}
}