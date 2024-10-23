<?php
namespace Catpow\SVG;
class Flux extends Shape{
	protected $default_atts=['stroke'=>'currentColor','fill'=>'none'];
	public function render(){
		srand($this->props['seed']??1);
		$num=$this->props['num']??rand(32,128);
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		$m=$height>>1;
		$c=$width>>1;
		$dx=$width>>3;
		$r=$this->props['r']??rand($m>>2,$m);
		
		$p=rand(0,360)/180*pi();
		$ps=rand(30,180)/180*pi();
		foreach(['px1','px2','py0','py1','py2','py3'] as $i=>$v){
			$$v=$this->props[$v]??($p+$ps*$i);
		}
		foreach(['sx1','sx2','sy0','sy1','sy2','sy3'] as $v){
			$$v=$this->props[$v]??rand(3,60)/1800*pi();
		}
		
		$opacity=$this->props['opacity']??rand(10,100)/100;
		$d='';
		for($i=0;$i<$num;$i++){
			$cx1=$c-$dx+sin($px1+$sx1*$i)*$dx;
			$cx2=$c+$dx+sin($px2+$sx2*$i)*$dx;
			$ay1=$m+sin($py0+$sy0*$i)*$r;
			$cy1=$m+sin($py1+$sy1*$i)*$r;
			$cy2=$m+sin($py2+$sy2*$i)*$r;
			$ay2=$m+sin($py3+$sy3*$i)*$r;
			$d.="M 0,{$ay1} C {$cx1},{$cy1} {$cx2},{$cy2} {$width},{$ay2}";
		}
		printf('<path class="%s" d="%s"%s/>',$this->className,$d,$this->get_attributes());
	}
}