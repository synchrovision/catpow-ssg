<?php
namespace Catpow\SVG;
abstract class Shape{
	public $className,$container,$props;
	protected $default_atts=[],$optional_atts=["clip-path","clip-rule","color","color-interpolation","color-rendering","cursor","display","fill","fill-opacity","fill-rule","filter","mask","opacity","pointer-events","shape-rendering","stroke","stroke-dasharray","stroke-dashoffset","stroke-linecap","stroke-linejoin","stroke-miterlimit","stroke-opacity","stroke-width","transform","vector-effect","visibility","style","id"];
	public function __construct($container,$props){
		$this->className=sprintf('svg-shape svg-shape-%s',strtolower(substr(strrchr(static::class,'\\'),1)));
		if(!empty($props['className'])){$this->className.=' '.$props['className'];}
		$this->container=$container;
		$this->props=$props;
	}
	public function render(){
		
	}
	public function get_attributes(){
		$atts=[];
		$rtn='';
		$atts=array_merge($atts,$this->default_atts,array_intersect_key($this->props,$this->default_atts));
		$atts=array_merge($atts,array_intersect_key($this->props,array_flip($this->optional_atts)));
		foreach($atts as $key=>$val){
			$rtn.=sprintf(' %s="%s"',$key,$val);
		}
		return $rtn;
	}
}