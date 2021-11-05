<?php
namespace Catpow;
class Site{
	public $info;
	private static $instance;
	private function __construct($info){
		$this->info=$info;
	}
	public static function init($info){
		return $GLOBALS['site']=static::$instance=new static($info);
	}
	public function __get($name){
		if(isset($this->info[$name])){return $this->info[$name];}
	}
}