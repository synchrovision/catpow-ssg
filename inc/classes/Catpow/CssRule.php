<?php
namespace Catpow;
abstract class CssRule{
	public $s,$selectors=[];
	protected function __construct($s){
		$this->s=(array)$s;
	}
	public static function section($name=null){
		if(empty($name)){return new static();}
		return new static(explode('-',$name));
	}
	public function export_selectors_file($file=null){
		if(empty($file)){$file=PAGE_TMPL_DIR.'/_scss/selectors/'.($this->s[0]??'style').'.scss';}
		$dir=dirname($file);
		if(!is_dir($dir)){mkdir($dir,0755,true);}
		file_put_contents($file,self::get_selectors_code($this->selectors));
	}
	public static function get_selectors_code($selectors,$indent=0){
		$code='';
		foreach($selectors as $sel=>$children){
			$code.=str_repeat("\t",$indent)."{$sel}{\n";
			$code.=self::get_selectors_code($children,$indent+1);
			$code.=str_repeat("\t",$indent)."}\n";
		}
		return $code;
	}
	abstract public function apply($html):string;
}