<?php
namespace Catpow\SVG;
use Catpow\Scss;
use Catpow\Calc;
class Erosion extends Shape{
	protected $default_atts=['fill'=>'currentColor'];
	public function render(){
		srand($this->props['seed']??1);
		$x=$this->props['x']??$this->container->x;
		$y=$this->props['y']??$this->container->y;
		$width=$this->props['width']??$this->container->width;
		$height=$this->props['height']??$this->container->height;
		$u=$this->props['u']??rand(3,10)*20;
		$p=$this->props['p']??rand(1,15);
		$m=$this->props['m']??pow(rand(0,16),2);
		$w=ceil($width/$u)+1;
		$h=ceil($height/$u)+1;
		$d='';
		foreach(self::get_cell_map($w,$h,$p,$m) as $cell){
			$d.=sprintf('M %d,%d l %3$d,0 0,%3$d -%3$d 0 z',$cell[0]*$u-$u/2,$cell[1]*$u-$u/2,$u-2);
		}
		printf('<path class="%s" d="%s"%s/>',$this->className,$d,$this->get_attributes());
	}
	protected function get_cell_map($w,$h,$p,$m){
		$rtn=[];
		$l=$w*$h;
		$c=(($p&$p<<1)?512:255)+$m;
		$t=$m>>1;
		$rands=Calc::random_uint8_array($l);
		for($y=0;$y<$h;$y++){
			for($x=0;$x<$w;$x++){
				$vals=[];
				if($p===0){$vals[]=0.5;}
				if($p&1){$vals[]=$x/$w;}
				if($p&2){$vals[]=($w-$x)/$w;}
				if($p&4){$vals[]=$y/$h;}
				if($p&8){$vals[]=($h-$y)/$h;}
				if(min($vals)*$c-$t>$rands[$w*$y+$x]){
					$rtn[]=[$x,$y];
				}
			}
		}
		return $rtn;
	}
}