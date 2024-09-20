<?php
namespace Catpow;
class Block{
	private static $slots=[];
	public $block,$part='',$props,$children,$dir;
	public function __construct($block,$props=[],$children=[]){
		list($this->block,$this->part)=explode('/',$block.'/');
		$this->props=$props;
		$this->children=$children;
		$this->dir=ABSPATH.'/blocks/'.$this->block;
	}
	public function init(){
		if(!is_dir($this->dir)){
			if(!is_dir(TMPL_DIR.'/blocks/'.$this->block) && is_dir(INC_DIR.'/blocks/'.$this->block)){
				$files=new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator(
						INC_DIR.'/blocks/'.$this->block,
						\RecursiveDirectoryIterator::SKIP_DOTS
					),
					\RecursiveIteratorIterator::SELF_FIRST
				);
				foreach($files as $file){
					if($file->is_dir()){
						mkdir(TMPL_DIR.'/blocks/'.$this->block.'/'.$files->getSubPathName(),0755);
					}
					else{
						copy($file,TMPL_DIR.'/blocks/'.$this->block.'/'.$files->getSubPathName());
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
	public static function convert($html){
		$doc=new \DOMDocument();
		$doc->loadHTML(mb_encode_numericentity($html,[0x80,0xffff,0,0xffff],'UTF-8'),\LIBXML_HTML_NOIMPLIED|\LIBXML_HTML_NODEFDTD|\LIBXML_NOERROR);
		foreach($doc->childNodes??[] as $el){
			self::_convert($el,$doc);
		}
		$html=mb_decode_numericentity($doc->saveHTML(),[0x80,0xffff,0,0xffff],'UTF-8');
		return $html;
	}
	private static function _convert($el,$doc){
		if(!is_a($el,\DOMElement::class) && !is_a($el,\DOMDocumentFragment::class)){return;}
		if(substr($el->tagName,0,6)==='block-'){
			$atts=['level'=>self::get_level($el)];
			foreach($el->attributes as $attr){
				$atts[$attr->name]=$attr->value;
			}
			$id=uniqid();
			$slot=$doc->createDocumentFragment();
			if($el->hasChildNodes()){
				while($el->childNodes->length){
					$slot->appendChild($el->childNodes->item(0));
				}
			}
			self::$slots[$id]=$slot;
			$block=new self(substr($el->tagName,6),$atts,sprintf('<slot id="%s"/>',$id));
			$block->init();
			$tmp=new \DOMDocument();
			$tmp->loadHTML(
				mb_encode_numericentity($block->get_html(),[0x80,0xffff,0,0xffff],'UTF-8'),
				\LIBXML_HTML_NOIMPLIED|\LIBXML_HTML_NODEFDTD|\LIBXML_NOERROR
			);
			$block_el=$doc->importNode($tmp->childNodes->item(0),true);
			$el->parentNode->replaceChild($block_el,$el);
			$el=$block_el;
		}
		elseif($el->tagName==='slot'){
			$id=$el->getAttribute('id');
			$slot=self::$slots[$id];
			$el->parentNode->replaceChild($slot,$el);
			$el=$slot;
			unset(self::$slots[$id]);
		}
		if($el->hasChildNodes()){
			for($i=0;$i<$el->childNodes->length;$i++){
				self::_convert($el->childNodes->item($i),$doc);
			}
		}
	}
	public static function get_level($el){
		if(is_a($el,\DOMElement::class) && $el->hasAttribute('level')){
			return $el->getAttribute('level');
		}
		if(is_a($el->parentNode,\DOMElement::class)){
			if($el->parentNode->hasAttribute('level')){
				return $el->parentNode->getAttribute('level')+1;
			}
			return self::get_level($el->parentNode);
		}
		return 0;
	}
}