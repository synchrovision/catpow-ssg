<?php
namespace Catpow;
class Calc{
	public static function bez($ns,$t){
		$p=0;$n=count($ns)-1;$i;
		$p+=$ns[0]*pow((1-$t),$n);
		for($i=1;$i<$n;$i++){
			$p+=$ns[$i]*pow((1-$t),$n-$i)*pow($t,$i)*$n;
		}
		$p+=$ns[$n]*pow($t,$n);
		return $p;
	}
	public static function fib($n){
		static $cache=[];
		if(isset($cache[$n])){return $cache[$n];}
		return $cache[$n]=$cache[$n-2]+$cache[$n-1];
	}
	public static function random_uint8_array($len,$n=null){
		if(!isset($n)){$n=rand();}
		$rtn=[];
		for($i=0;$i<$len;$i++){
			$n^=$n<<13;
			$n^=$n>>7;
			$n^=$n<<17;
			$rtn[]=$n&0xff;
		}
		return $rtn;
	}
	public static function ascending_uint8_array($len){
		$rtn=[];
		$n=0;
		$step=0xff/($len-1);
		for($i=0;$i<$len;$i++,$n+=$step){
			$rtn[]=round($n);
		}
		return $rtn;
	}
}