<?php
namespace Catpow;
class Page{
	public $uri,$info,$scripts,$styles;
	private static $instance;
	private function __construct($uri,$info){
		$this->uri=$uri;
		$this->scripts=new Deps('js');
		$this->styles=new Deps('css');
		if(empty($info)){
			
		}
		$this->info=$info;
	}
	public static function init($uri,$info=null){
		return $GLOBALS['page']=static::$instance=new static($uri,$info);
	}
	public function use_block($block){
		if(Block::get_block_file($block,'app/index.jsx')){
			$this->scripts->enqueue('/blocks/'.$block.'/app.js');
		}
		if(Block::get_block_file($block,'script.jsx') || Block::get_block_file($block,'script.js')){
			$this->scripts->enqueue('/blocks/'.$block.'/script.js');
		}
		if(Block::get_block_file($block,'style.scss') || Block::get_block_file($block,'style.css')){
			$this->styles->enqueue('/blocks/'.$block.'/style.css');
		}
	}
	public function render_deps(){
		$this->scripts->render();
		$this->styles->render();
	}
}