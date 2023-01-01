<?php
namespace Catpow\SVG;
use Catpow\Scss;
class RandomCircle extends Shape{
	public function render(){
		srand($this->props['seed']??1);
		$colors=$this->props['colors']??['m_-1','m_0','m_1'];
		$num=$this->props['num']??rand(count($colors),16);
		$x=$this->props['x']??$this->container->x;
		$y=$this->props['y']??$this->container->y;
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		$u=min($width,$height)>>1;
		$min=$this->props['min']??rand($u>>3,$u>>1);
		$max=$this->props['max']??rand($u>>1,$u);
		$atts=$this->get_attributes();
		foreach($colors as $i=>$color){
			if(preg_match('/^([\w_\-]+)( (\d+))?( (0?\.\d+))?$/',$color,$matches)){
				if($color=Scss::translate_color($matches[1],$matches[3]??null,$matches[5]??null)){
					$colors[$i]=$color;
				}
			}
		}
		printf('<g class="%s"/>',$this->className);
		for($i=0;$i<$num;$i++){
			$r=rand($min,$max);
			$x0=$x+rand(0,$width);
			$y0=$y+rand(0,$height);
			printf('<circle fill="%s" cx="%s" cy="%s" r="%s"%s/>',$colors[$i%count($colors)],$x0,$y0,$r,$atts);
		}
		echo '</g>';
	}
}