<?php
namespace Catpow;
class SVG{
	public $block,$props,$className,$viewBox,$x,$y,$width,$height,$preserveAspectRatio,$defs=[],$children=[];
	public function __construct($props=[],$children=[]){
		$this->props=$props;
		$this->viewBox=$props['viewBox']??'0 0 1000 1000';
		$this->className='svg-container';
		if(!empty($props['className'])){$this->className.=' '.$props['className'];}
		$this->preserveAspectRatio=$props['preserveAspectRatio']??'xMidYMid slice';
		list($this->x,$this->y,$this->width,$this->height)=explode(' ',$this->viewBox);
		if(is_a($children,\Closure::class)){$children($this);}
		else if(is_array($children)){$this->children=$children;}
	}
	public function add($class,$props){
		$className='Catpow\\SVG\\'.$class;
		$this->children[]=new $className($this,$props);
	}
	public function render(){
		printf(
			'<svg class="%s" viewBox="%s" preserveAspectRatio="%s" xmlns="http://www.w3.org/2000/svg">'."\n",
			$this->className,$this->viewBox,$this->preserveAspectRatio
		);
		foreach($this->children as $key=>$child){
			if(is_array($child)){
				if(!is_numeric($key)){
					$child['id']=$key;
				}
				$class=isset($child['class'])?$child['class']:array_shift($child);
				if(ctype_upper($class[0])){
					$className='Catpow\\SVG\\'.$class;
					$child=new $className($this,$child);
				}
				else{
					unset($child['class']);
					$atts='';
					foreach($child as $key=>$val){$atts.=sprintf(' %s="%s"',$key,$val);}
					$child=sprintf('<%s%s/>',$class,$atts);
				}
			}
			if(is_string($child)){echo $child;continue;}
			if(method_exists($child,'render')){$child->render();}
		}
		echo "</svg>\n";
	}
}