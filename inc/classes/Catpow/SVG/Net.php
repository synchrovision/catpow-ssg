<?php
namespace Catpow\SVG;
class Net extends Shape{
	protected $default_atts=['stroke'=>'currentColor'];
	public function render(){
		srand($this->props['seed']??1);
		$num=$this->props['num']??rand(64,255);
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		
		$r=(int)sqrt($width*$height/$num);
		
		$points=[];
		for($i=0;$i<$num;$i++){
			$np=[rand(-$r,$width+$r),rand(-$r,$height+$r)];
			foreach($points as $p){
				if(abs($np[0]-$p[0])<$r && abs($np[1]-$p[1])<$r){continue 2;}
			}
			$points[]=$np;
		}
		
		$d='';
		$num=count($points);
		for($i=0;$i<$num;$i++){
			for($j=$i+1;$j<$num;$j++){
				$p1=$points[$i];
				$p2=$points[$j];
				if(hypot($p1[0]-$p2[0],$p1[1]-$p2[1])>$r*4){continue;}
				$d.="M {$p1[0]},{$p1[1]} L {$p2[0]},{$p2[1]}";
			}
		}
		printf('<path class="%s" d="%s"%s/>',$this->className,$d,$this->get_attributes());
	}
}