<?php
namespace Catpow;
class Page{
	public $uri,$dir_uri,$info,$scripts,$styles;
	private static $instance;
	private function __construct($uri,$info){
		$this->uri=$uri;
		$this->dir=(substr($uri,-1)==='/')?$uri:dirname($uri).'/';
		$this->scripts=new Deps('js');
		$this->styles=new Deps('css');
		$this->info=$info??$GLOBALS['sitemap'][$uri]??null;
	}
	public static function init($uri,$info=null){
		return $GLOBALS['page']=static::$instance=new static($uri,$info);
	}
	public function get_the_page_file($file){
		if(file_exists($f=ABSPATH.$this->dir.$file)){return $f;}
		if(file_exists($f=TMPL_DIR.$this->dir.$file)){return $f;}
		return false;
	}
	public function get_the_file($file){
		if(substr($file,0,1)==='/'){
			if(file_exists($f=ABSPATH.$file)){return $f;}
			if(file_exists($f=TMPL_DIR.$file)){return $f;}
			if(file_exists($f=INC_DIR.'/'.$file)){return $f;}
			return null;
		}
		if(file_exists($f=ABSPATH.$this->dir.$file)){return $f;}
		if(file_exists($f=TMPL_DIR.$this->dir.$file)){return $f;}
		if(file_exists($f=ABSPATH.'/'.$file)){return $f;}
		if(file_exists($f=TMPL_DIR.'/'.$file)){return $f;}
		if(file_exists($f=INC_DIR.'/'.$file)){return $f;}
	}
	public function get_file_path_for_uri($uri){
		if(substr($uri,0,1)==='/'){return ABSPATH.$uri;}
		return ABSPATH.$this->dir.$uri;
	}
	public function generate_webp_for_image($image){
		$file=$this->get_the_file($image);
		if(empty($file)){return false;}
		switch(strrchr($image,'.')){
			case '.jpg':
			case '.jpeg':
				$im=imagecreatefromjpeg($file);break;
			case '.png':
				$im=imagecreatefrompng($file);break;
			case '.gif':
				$im=imagecreatefromgif($file);break;
			default:
				return false;
		}
		$dest_dir=dirname($this->get_file_path_for_uri($image));
		if(!is_dir($dest_dir)){mkdir($dest_dir,0755,true);}
		imagewebp($im,preg_replace('/\.\w+$/','.webp',$file));
		return preg_replace('/\.\w+$/','.webp',$image);
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
	public function __get($name){
		if(isset($this->info[$name])){return $this->info[$name];}
	}
}