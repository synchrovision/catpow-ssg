<?php
namespace Catpow;
class Block{
	public $block,$part='',$props,$children,$dir;
	public function __construct($block,$props=[],$children=[]){
		list($this->block,$this->part)=explode('/',$block.'/');
		$this->props=$props;
		$this->children=$children;
		$this->dir=ABSPATH.'/blocks/'.$this->block;
	}
	public function init(){
		if(!is_dir($this->dir)){
			if(!is_dir(TMPL_DIR.'/blocks/'.$block) && is_dir(INC_DIR.'/blocks/'.$block)){
				$files=new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(
						INC_DIR.'/blocks/'.$block,
						\RecursiveDirectoryIterator::SKIP_DOTS
					),
					\RecursiveIteratorIterator::SELF_FIRST
				);
				foreach($files as $file){
					if($file->is_dir()){
						mkdir(TMPL_DIR.'/blocks/'.$block.'/'.$files->getSubPathName(),0755);
					}
					else{
						copy($file,TMPL_DIR.'/blocks/'.$block.'/'.$files->getSubPathName());
					}
				}
			}
			mkdir($this->dir,0755,1);
		}
		if($f=self::get_block_file($this->block,'block.json')){
			$conf=json_decode(file_get_contents($f));
		}
		if(empty($conf)){$conf=[];}
	}
	public function get_html(){
		global $page;
		ob_start();
		extract($this->props);
		if(!empty($page)){$page->use_block($this->block);}
		$className=(empty($className)?'':$className.' ').'block-'.$this->block;
		$children=is_array($this->children)?implode("\n",iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->children)),false)):$this->children;
		if(is_a($children,\Closure::class)){
			ob_start();
			$children($this->props);
			$children=ob_get_clean();
		}
		if(empty($this->part)){
			include self::get_block_file($this->block,'block.php');
		}
		else{
			$className.='-'.$this->part;
			include self::get_block_file($this->block,'block-'.$this->part.'.php');
		}
		return ob_get_clean();
	}
	public static function get_block_file($block,$file){
		if(file_exists($f=ABSPATH.'/blocks/'.$block.'/'.$file)){return $f;}
		if(file_exists($f=TMPL_DIR.'/blocks/'.$block.'/'.$file)){return $f;}
		if(file_exists($f=INC_DIR.'/blocks/'.$block.'/'.$file)){return $f;}
		return false;
	}
}