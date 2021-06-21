<?php
namespace Catpow;
class Block{
	public $block,$props,$children,$dir;
	public function __construct($block,$props=[],$children=[]){
		$this->block=$block;
		$this->props=$props;
		$this->children=$children;
		$this->dir=ABSPATH.'blocks/'.$block;
	}
	public function init(){
		if(!is_dir($this->dir)){mkdir($this->dir,0755,1);}
		if($f=self::get_block_file($this->block,'block.json')){
			$conf=json_decode(file_get_contents($f));
		}
		if(empty($conf)){$conf=[];}
		global $page;
		if($f=self::get_block_file($this->block,'style.scss')){
			if(!file_exists($this->dir.'/style.scss')){
				copy($f,$this->dir.'/style.scss');
			}
		}
		if($f=self::get_block_file($this->block,'script.js')){
			if(!file_exists($this->dir.'/script.js')){
				copy($f,$this->dir.'/script.js');
			}
		}
	}
	public function get_html(){
		global $page;
		ob_start();
		extract($this->props);
		if(!empty($page)){$page->use_block($this->block);}
		$className='block-'.$this->block.(empty($className)?'':' '.$className);
		$children=is_array($this->children)?implode("\n",$this->children):$this->children;
		include self::get_block_file($this->block,'block.php');
		return ob_get_clean();
	}
	public static function get_block_file($block,$file){
		if(file_exists($f=ABSPATH.'/blocks/'.$block.'/'.$file)){return $f;}
		if(file_exists($f=TMPL_DIR.'/blocks/'.$block.'/'.$file)){return $f;}
		if(file_exists($f=INC_DIR.'/blocks/'.$block.'/'.$file)){return $f;}
		return false;
	}
}