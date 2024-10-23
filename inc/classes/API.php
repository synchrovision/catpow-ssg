<?php
namespace Catpow;
class API{
	public static function request($path,$req){
		$class=self::get_ep_class_for_path($path);
		if(!class_exists($class)){return false;}
		$res=$class::request($req);
		header('Content-type:application/json');
		echo json_encode($res,0500);
	}
	public static function get_ep_class_for_path($path){
		$class='\\Catpow\\API';
		foreach(explode('/',trim($path,'/')) as $chunk){
			$class.='\\';
			foreach(explode('_',$chunk) as $word){
				$class.=ucfirst($word);
			}
		}
		return $class;
	}
}