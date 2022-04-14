<?php
namespace Catpow;
class RSCSS extends CssRule{
	public $s=['sec'],$b=[],$e=[],$selectors=[];
	protected $org_s;
	public function add_selector($sel=null){
		$bsel='$this->selectors[".'.implode('"]["&-',$this->s).'"]';
		if(!empty($this->b)){$bsel.='["&-'.implode('"]["&-',$this->b).'"]';}
		if(!empty($this->e)){$bsel.='[">.'.implode('"][">.',$this->e).'"]';}
		if(isset($sel)){$bsel.='["'.$sel.'"]';}
		if(eval("return !isset({$bsel});")){eval($bsel."=[];");}
	}
	
	public function apply($html):string{
		$this->org_s=$this->s;
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
			$el->setAttribute('class',$el->tagName);
		}
		$classes=explode(' ',$el->getAttribute('class'));
		$_ms=[];
		foreach($classes as $i=>$class){
			if(substr($class,-1)==='-'){
				$_b=substr($class,0,-1);
				$_bi=$i;
			}
			elseif(substr($class,0,1)==='_'){
				$this->selectors[':root']['.'.$class]=[];
			}
			elseif(substr($class,0,1)==='-'){
				$_ms[]=$class;
			}
			elseif(empty($_e) && preg_match('/^[a-zA-Z]+$/',$class)){
				$_e=$class;
			}
			elseif(empty($_b) && preg_match('/^([a-zA-Z]+)((-[a-zA-Z]+)+)$/',$class,$matches)){
				$_s=$matches[1];
				$_b=substr($matches[2],1);
				$_bi=$i;
			}
		}
		if(!empty($_s)){
			$prev_s=$this->s;
			$this->s=[$_s];
		}
		if(empty($_b)){
			if(empty($_e)){
				$_e=$el->tagName;
				$classes[]=$_e;
				$el->setAttribute('class',implode(' ',$classes));
			}
			$prev_e=$this->e;
			$this->e[]=$_e;
			$this->add_selector();
		}
		else{
			$prev_b=$this->b;
			if(substr($_b,0,1)==='-'){
				$this->b[]=substr($_b,1);
			}
			else{
				if(empty($_s)){
					$prev_s=$this->s;
					$this->s=$this->org_s;
				}
				$this->b=explode('-',$_b);
			}
			$classes[$_bi]=implode('-',$this->s).'-'.implode('-',$this->b);
			$el->setAttribute('class',implode(' ',$classes));
			$prev_e=$this->e;
			$this->e=[];
			$this->add_selector();
		}
		foreach($_ms as $_m){
			$this->add_selector('&.'.$_m);
		}
		foreach($el->childNodes??[] as $child_el){
			$this->_apply($child_el);
		}
		if(isset($prev_s)){$this->s=$prev_s;}
		if(isset($prev_b)){$this->b=$prev_b;}
		if(isset($prev_e)){$this->e=$prev_e;}
	}
}