<?php
namespace Catpow\SVG;
use Catpow\Scss;
class Gradient extends SVG{
	protected $container,$props,$type;
	protected $default_atts=[],$optional_atts=['gradientUnits','gradientTransform','spreadMethod','href','style'];
	public function __construct($container,$props){
		$this->type=$props['type']??'linear';
		$this->container=$container;
		$this->props=$props;
		$this->className='svg-gradient';
		if(!empty($props['className'])){$this->className.=' '.$props['className'];}
		if($this->type==='linear'){
			$this->default_atts=['x1'=>0,'y1'=>0,'x2'=>0,'y2'=>1];
			array_push($this->optional_atts,'fr','fx','fy');
		}
		else{
			$this->default_atts=['cx'=>0.5,'cy'=>0.5,'r'=>0.5];
			if(isset($props['deg'])){
				$this->default_atts=array_merge(
					$this->default_atts,
					self::get_linear_gradient_anchor_points_from_degree($this->props['deg'])
				);
			}
		}
		$this->default_atts['id']='svg-gradient-'.base_convert(md5(serialize($this)),16,36);
	}
	public function render(){
		printf('<defs><%sGradient"%s>',$this->type,$this->get_attributes());
		foreach($this->get_stops() as $offset=>$stop){
			if(is_string($stop)){
				$stop=static::parse_stop($stop);
			}
			$className='svg-stop';
			if(!empty($stop['className'])){
				$className.=' '.$stop['className'];
			}
			printf(
				'<stop class="%s" offset="%s"%s%s/>',
				$className,$offset,
				empty($stop['color'])?'':' stop-color="'.$stop['color'].'"',
				empty($stop['opacity'])?'':' stop-opacity="'.$stop['opacity'].'"'
			);
		}
		printf('</%sGradient></defs>',$this->type);
	}
	protected function get_stops(){
		if(!empty($this->props['stops'])){return $this->props['stops'];}
		return ['0%'=>'m_-1','100%'=>'m_1'];
	}
	public static function parse_stop($stop){
		if(preg_match('/^\.(.+)( (\d?\.\d+))?$/',$stop,$matches)){
			return ['className'=>$matches[1],'color'=>'currentColor','opacity'=>$matches[3]??null];
		}
		
		if(preg_match('/^([\w_\-]+)( (\d+))?( (0?\.\d+))?$/',$stop,$matches)){
			if($color=Scss::translate_color($matches[1],$matches[3]??null,$matches[5]??null)){
				return ['color'=>$color];
			}
		}
		return ['color'=>$stop];
	}
	public static function get_linear_gradient_anchor_points_from_degree($deg){
		$rad1=$deg/180*pi();
		$rad2=abs(M_PI_4-($deg%90)/180*M_PI);
		$r=M_SQRT1_2*cos($rad2);
		$px=$r*cos($rad1);
		$py=$r*sin($rad1);
		return ['x1'=>0.5-$px,'y1'=>0.5+$py,'x2'=>0.5+$px,'y2'=>0.5-$py];
	}
}