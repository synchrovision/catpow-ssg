<?php
namespace Catpow;
class Block{
	private static $slots=[],$schemas=[];
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
			$block_name=substr($el->tagName,6);
			if(!isset(self::$schemas[$block_name])){
				self::$schemas[$block_name]=empty($schema_file=self::get_block_file($block_name,'schema.json'))?false:json_decode(file_get_contents($schema_file),true);
			}
			$atts=self::extract_atts($el,$doc,self::$schemas[$block_name]);
			$atts['level']=self::get_level($el);
			$id=uniqid();
			$slot=$doc->createDocumentFragment();
			if($el->hasChildNodes()){
				while($el->childNodes->length){
					$slot->appendChild($el->childNodes->item(0));
				}
			}
			self::$slots[$id]=$slot;
			$block=new self($block_name,$atts,sprintf('<slot id="%s"/>',$id));
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
		elseif($el->tagName==='rtf' || $el->tagName==='rxf' || $el->tagName==='md'){
			$tmp=new \DOMDocument();
			$html=$el->hasAttribute('file')?file_get_contents(Page::get_instance()->get_the_file($el->getAttribute('file'))):'';
			if($el->hasChildNodes()){
				$frag=$doc->createDocumentFragment();
				while($el->childNodes->length){
					$frag->appendChild($el->childNodes->item(0));
				}
				$html.=mb_decode_numericentity($tmp->saveHTML($tmp->importNode($frag,true)),[0x80,0xffff,0,0xffff],'UTF-8');
				if(preg_match('/^(\n\s+)/mu',$html,$matches)){
					$html=str_replace($matches[1],"\n",$html);
				}
			}
			$html=('Catpow\\'.$el->tagName)($html,$el->hasAttribute('class')?$el->getAttribute('class'):$el->tagName);
			$tmp->loadHTML(
				mb_encode_numericentity('<tmp>'.$html.'</tmp>',[0x80,0xffff,0,0xffff],'UTF-8'),
				\LIBXML_HTML_NOIMPLIED|\LIBXML_HTML_NODEFDTD|\LIBXML_NOERROR
			);
			$tmp_el=$doc->importNode($tmp->childNodes->item(0),true);
			$frag=$doc->createDocumentFragment();
			while($tmp_el->childNodes->length){
				$frag->appendChild($tmp_el->childNodes->item(0));
			}
			$el->parentNode->replaceChild($frag,$el);
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
	public static function extract_atts($el,$doc,$schema=null){
		if(empty($schema) || isset($schema['properties'])){
			$atts=[];
			foreach($el->attributes as $attr){
				$atts[$attr->name]=$attr->value;
			}
			if(empty($schema)){return $atts;}
			if($el->hasChildNodes()){
				for($i=0;$i<$el->childNodes->length;$i++){
					$child=$el->childNodes->item($i);
					if(!is_a($child,\DOMElement::class)){continue;}
					if(isset($schema['properties'][$child->tagName])){
						if(isset($schema['properties'][$child->tagName]['key'])){
							self::array_set_value(
								$atts,$schema['properties'][$child->tagName]['key'],
								self::extract_atts($child,$doc,$schema['properties'][$child->tagName])
							);
						}
						else{
							$atts[$child->tagName]=self::extract_atts($child,$doc,$schema['properties'][$child->tagName]);
						}
						$el->removeChild($child);
						$i--;
					}
				}
				if(empty($schema['properties']['children']) && $el->hasChildNodes()){
					$id=uniqid();
					$slot=$doc->createDocumentFragment();
					while($el->childNodes->length){
						$slot->appendChild($el->childNodes->item(0));
					}
					self::$slots[$id]=$slot;
					$atts['children']=sprintf('<slot id="%s"/>',$id);
				}
			}
			foreach($atts as $key=>$val){
				if(!empty($schema['properties'][$key]['items']) && is_string($val)){
					$atts[$key]=csv($val)->items;
				}
			}
			return $atts;
		}
		if(isset($schema['items'])){
			$items=[];
			while($el->childNodes->length){
				$child=$el->childNodes->item(0);
				if(is_a($child,\DOMElement::class)){
					$items[]=self::extract_atts($child,$doc,$schema['items']);
				}
				$el->removeChild($child);
			}
			return $items;
		}
		if(isset($schema['type']) && in_array($schema['type'],['bool','intger','number','string'])){
			$value=$el->hasAttribute('value')?$el->getAttribute('value'):$el->textContent;
			switch($schema['type']){
				case 'bool':return !in_array(strtolower(trim($value)),['0','false','no']);
				case 'intger':return (int)$value;
				case 'number':return (float)$value;
				default: $value;
			}
		}
		$id=uniqid();
		$slot=$doc->createDocumentFragment();
		if($el->hasChildNodes()){
			while($el->childNodes->length){
				$slot->appendChild($el->childNodes->item(0));
			}
		}
		self::$slots[$id]=$slot;
		return sprintf('<slot id="%s"/>',$id);
	}
	protected static function array_set_value(&$array,$key,$value){
		if(strpos($key,'[')===false){$array[$key]=$value;}
		elseif(preg_match('/^(\w+)((\[\w+\])*)(\[\])?$/',$key,$matches)){
			$ref=&$array[$matches[1]];
			if(!empty($matches[2])){
				$keys=explode('][',substr($matches[2],1,-1));
				foreach($keys as $key){
					$ref=&$ref[$key];
				}
			}
			if(empty($matches[4])){
				$ref=$value;
			}
			else{
				$ref[]=$value;
			}
			
		}
		else{
			error_log("{$key} is not valid key");
		}
	}
}