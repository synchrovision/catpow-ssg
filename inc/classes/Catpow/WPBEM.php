<?php
namespace Catpow;
class WPBEM extends CssRule{
	public $s=['cp'],$b,$e,$parent,$b_stuck=[],$selectors=[];
	
	public function get_class(){
		if(empty($this->b)){return implode('-',$this->s);}
		$b_class=implode('-',$this->s).'-'.implode('-',$this->b);
		if(empty($this->e)){return $b_class;}
		return $b_class.'__'.implode('-',$this->e);
	}
	public function add_selector($bem=null){
		if(empty($bem)){$bem=$this;}
		$sel='$this->selectors';
		$sel.="['.".implode("']['&-",$bem->s)."']['&-".implode("']['&-",$bem->b)."']";
		if(!empty($bem->e)){
			$sel.="['&__".implode("']['&-",$this->e)."']";
		}
		if(eval("return empty({$sel});")){eval($sel."=[];");}
	}
	
	public function apply($html):string{
		$html=preg_replace('/ @([\w\.\-:]+=)/',' x-on:$1',$html);
		$doc=new \DOMDocument();
		$doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'),\LIBXML_HTML_NOIMPLIED|\LIBXML_HTML_NODEFDTD|\LIBXML_NOERROR);
		foreach($doc->childNodes??[] as $el){
			$this->_apply($el);
		}
		$html=mb_convert_encoding($doc->saveHTML(),'UTF-8','HTML-ENTITIES');
		$html=str_replace('<br>','<br/>',$html);
		$html=preg_replace('/><\/(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)>/','/>',$html);
		$html=preg_replace('/ x\-on:([\w\.\-:]+=)/',' @$1',$html);
		return $html;
	}
	private function _apply($el){
		if(!is_a($el,\DOMElement::class)){return;}
		if(empty($el->getAttribute('class'))){
			if(in_array($el->tagName,['br','link','script','source','template'],true)){return;}
			$el->setAttribute('class','_'.$el->tagName);
		}
		$classes=explode(' ',$el->getAttribute('class')??'');
		$_s=$_b=$_e=false;
		foreach($classes as $i=>$class){
			if(substr($class,-1)==='-'){
				$this->b_stuck[]=[$this->b,$this->e];
				$this->b=[substr($class,0,-1)];
				$this->e=[];
				$_b=true;
			}
			if(substr($class,0,1)==='-'){
				$this->b_stuck[]=[$this->b,$this->e];
				$this->b[]=substr($class,1);
				$this->e=[];
				$_b=true;
			}
			else if(substr($class,0,1)==='_'){
				$this->e[]=substr($class,1);
				$this->add_selector();
				$_e=true;
			}
			if($_s||$_b||$_e){
				$classes[$i]=$this->get_class();
				$el->setAttribute('class',implode(' ',$classes));
				break;
			}
		}
		foreach($el->childNodes??[] as $child_el){
			$this->_apply($child_el);
		}
		if($_s){array_pop($this->s);$this->b=[];$this->e=[];}
		if($_b){list($this->b,$this->e)=array_pop($this->b_stuck);}
		if($_e){array_pop($this->e);}
	}
}