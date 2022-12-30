<?php
namespace Catpow\SVG;
class BG extends Shape{
	protected $default_atts=['x'=>0,'y'=>0,'width'=>'100%','height'=>'100%'];
	public function render(){
		printf('<rect class="%s"%s/>',$this->className,$this->get_attributes());
	}
}