<?php
namespace Catpow\SVG;
use Catpow\Scss;
class SVG{
	protected $block,$props,$className,$viewBox,$x,$y,$width,$height,$preserveAspectRatio,$children=[];
	protected $default_atts=['preserveAspectRatio'=>'xMidYMid slice'],$optional_atts=["clip-path","clip-rule","color","color-interpolation","color-rendering","cursor","display","fill","fill-opacity","fill-rule","filter","mask","opacity","pointer-events","shape-rendering","stroke","stroke-dasharray","stroke-dashoffset","stroke-linecap","stroke-linejoin","stroke-miterlimit","stroke-opacity","stroke-width","transform","vector-effect","visibility","style","id"],$color_atts=['color','fill','stroke'];
	public function __construct($props=[],$children=[]){
		$this->props=$props;
		$this->viewBox=$props['viewBox']??'0 0 1000 1000';
		$this->className='svg-container';
		if(!empty($props['className'])){$this->className.=' '.$props['className'];}
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
			'<svg class="%s" viewBox="%s" width="%s" height="%s" xmlns="http://www.w3.org/2000/svg"%s>'."\n",
			$this->className,$this->viewBox,$this->width,$this->height,$this->get_attributes()
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
					foreach($child as $key=>$val){
						$atts.=sprintf(' %s="%s"',$key,$val);
					}
					$child=sprintf('<%s%s/>',$class,$atts);
				}
			}
			if(is_string($child)){echo $child;continue;}
			if(method_exists($child,'render')){$child->render();}
		}
		echo "</svg>\n";
	}
	public function get_attributes(){
		$atts=[];
		$rtn='';
		$atts=array_merge($atts,$this->default_atts,array_intersect_key($this->props,$this->default_atts));
		$atts=array_merge($atts,array_intersect_key($this->props,array_flip($this->optional_atts)));
		foreach($atts as $key=>$val){
			if(in_array($key,$this->color_atts,true)){
				if(preg_match('/^([\w_\-]+)( (\d+))?( (0?\.\d+))?$/',$val,$matches)){
					if($color=Scss::translate_color($matches[1],$matches[3]??null,$matches[5]??null)){
						$val=$color;
					}
				}
			}
			$rtn.=sprintf(' %s="%s"',$key,$val);
		}
		return $rtn;
	}
}