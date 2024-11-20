<?php
namespace Catpow;

class HTML{
	static $attribute_delimiters=[
		'srcset'=>',',
		'coords'=>',',
		'accept'=>',',
		'content'=>';'
	];
	public static function parse_tag_data($tag_data){
		$rtn=[];
		if(preg_match('/^\w+/',$tag_data,$matches)){$rtn['tag']=$matches[0];}
		$base_tag_data=preg_replace('/\[.+?\]/','',$tag_data);
		if(preg_match('/(?<=#)[\w\-_]+/',$base_tag_data,$matches)){$rtn['id']=$matches[0];}
		if(preg_match_all('/(?<=\.)[\w\-_]+/',$base_tag_data,$all_matches)){$rtn['class']=implode(' ',$all_matches[0]);}
		if(preg_match_all('/\[([\w\-_]+)="?(.+?)"?\]/',$tag_data,$all_matches)){
			foreach($all_matches[1] as $i=>$key){
				$rtn[$key]=$all_matches[2][$i];
			}
		}
		return $rtn;
	}
	public static function get_attr_code($attr,$args=null){
		$rtn='';
		foreach($attr as $key=>$val){
			if($key==='tag'){continue;}
			if(!is_string($val) && !is_numeric($val) && is_callable($val)){$val=$val($attr,$args);}
			if(empty($val) && $val!=0){continue;}
			if(is_array($val)){
				if($key==='style'){
					$css='';
					foreach($val as $k=>$v){
						$css.=sprintf('%s:%s;',$k,$v);
					}
					$val=$css;
				}
				else{
					$vals=[];
					foreach($val as $k=>$v){
						if(is_numeric($k)){
							$vals[]=$v;
						}
						elseif(!empty($v)){
							$vals[]=$k;
						}

					}
					$val=implode(self::$attribute_delimiters[$key]??' ',$vals);
				}
			}
			$rtn.=sprintf(' %s="%s"',$key,$val);
		}
		return $rtn;
	}
}


?>