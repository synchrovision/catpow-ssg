<?php
namespace Catpow\SVG;
use Catpow\Scss;
class Shape extends SVG{
	public $className,$container,$props;
	public function __construct($container,$props){
		$this->className=sprintf('svg-shape svg-shape-%s',strtolower(substr(strrchr(static::class,'\\'),1)));
		if(!empty($props['className'])){$this->className.=' '.$props['className'];}
		$this->container=$container;
		$this->props=$props;
	}
	public function render(){
		
	}
}