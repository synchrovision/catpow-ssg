<?php
namespace Catpow\SVG;
use Catpow\Scss;
class Filter extends SVG{
	protected $default_atts=[];
	public $container,$props,$filters;
	public function __construct($container,$props){
		$this->container=$container;
		$this->props=$props;
		$this->className='svg-filter';
		if(!empty($props['className'])){$this->className.=' '.$props['className'];}
		$this->default_props['id']='svg-filter-'.base_convert(md5(serialize($this)),16,36);
		$this->init();
	}
	protected function init(){
		$this->filters=$this->props['filters']??[];
	}
	public function render(){
		printf('<filter class="%s"%s>',$this->className,$this->get_attributes());
		foreach($this->filters as $filter){
			if(is_string($filter)){echo $filter;}
			else{
				echo self::get_filter_code(array_shift($filter),$filter);
			}
		}
		echo '</filter>';
	}
	public static function get_filter_code($filter,$props){
		$atts='';
		switch($filter){
			case 'shadow':{
				$filter='feDropShadow';
				$d=$props['d']??8;
				$props['dx']=0;
				$props['dy']=$d;
				$props['stdDeviation']=$d*2+1;
				$props['flood-color']=Scss::translate_color('shd');
				unset($props['d']);
				break;
			}
			case 'blur':{
				$filter='feGaussianBlur';
				$props['stdDeviation']=$props['d']??8;
				break;
			}
		}
		foreach($props as $key=>$val){$atts.=sprintf(' %s="%s"',$key,$val);}
		return sprintf('<%s%s/>',$filter,$atts);
	}
}