<?php
namespace Catpow\SVG;
class Gradient{
	protected $container,$id,$props,$type;
	public function __construct($container,$props){
		$this->type=$props['type']??'linear';
		$this->container=$container;
		$this->props=$props;
		$this->id=$props['id']??'svg-gradient-'.base_convert(md5(serialize($this)),16,36);
	}
	public function render(){
		printf('<defs><%sGradient id="%s" "%s>',$this->type,$this->id,$this->get_attributes());
		foreach($this->get_stops() as $offset=>$stop){
			if(is_string($stop)){
				$stop=static::parse_stop($stop);
			}
			printf(
				'<stop class="svg-stop %s" offset="%s"%s/>',
				$stop['className'],$offset,
				empty($stop['opacity'])?'':' stop-opacity="'.$stop['opacity'].'"'
			);
		}
		printf('</%sGradient></defs>',$this->type);
	}
	public function get_id(){
		return $this->id;
	}
	protected function get_stops(){
		if(!empty($this->props['stops'])){return $this->props['stops'];}
		return [
			'0%'=>'is-color_-1',
			'100%'=>'is-color_1',
		];
	}
	public static function parse_stop($stop){
		if(preg_match('/^(.+) (\d?\.\d+)$/',$stop,$matches)){
			return ['className'=>$matches[1],'opacity'=>$matches[2]];
		}
		return ['className'=>$stop];
	}
	public static function get_linear_gradient_anchor_points_from_degree($deg){
		$rad1=$deg/180*pi();
		$rad2=abs(M_PI_4-($deg%90)/180*M_PI);
		$r=M_SQRT1_2*cos($rad2);
		$px=$r*cos($rad1);
		$py=$r*sin($rad1);
		return ['x1'=>0.5-$px,'y1'=>0.5+$py,'x2'=>0.5+$px,'y2'=>0.5-$py];
	}
	public function get_attributes(){
		$atts=[];
		$rtn='';
		$optional_atts=['gradientUnits','gradientTransform','spreadMethod','href','style'];
		if($this->type==='radial'){
			$default_atts=['cx'=>0.5,'cy'=>0.5,'r'=>0.5];
			$optional_atts=array_merge($optional_atts,['fr','fx','fy']);
		}
		else{
			$default_atts=['x1'=>0,'y1'=>0,'x2'=>0,'y2'=>1];
			if(isset($this->props['deg'])){
				$default_atts=array_merge($default_atts,self::get_linear_gradient_anchor_points_from_degree($this->props['deg']));
			}
		}
		$atts=array_merge($atts,$default_atts,array_intersect_key($this->props,$default_atts));
		$atts=array_merge($atts,array_intersect_key($this->props,array_flip($optional_atts)));
		foreach($atts as $key=>$val){
			$rtn.=sprintf(' %s="%s"',$key,$val);
		}
		return $rtn;
	}
}