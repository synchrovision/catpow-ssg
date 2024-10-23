<?php
namespace Catpow;
class WPBEM extends CssRule{
	public $s=['cp'],$b,$e,$parent,$b_stuck=[],$e_stuck=[],$selectors=[];
	
	public function get_class(){
		if(empty($this->b)){return implode('-',$this->s);}
		$b_class=implode('-',$this->s).'-'.implode('-',$this->b);
		if(empty($this->e)){return $b_class;}
		return $b_class.'__'.implode('-',$this->e);
	}
	public function add_selector($bem=null){
		if(empty($bem)){$bem=$this;}
		if(empty($bem->b)){return;}
		$sel='$this->selectors';
		$sel.="['.".implode("']['&-",$bem->s)."']['&-".implode("']['&-",$bem->b)."']";
		if(!empty($bem->e)){
			$sel.="['&__".implode("']['&-",$bem->e)."']";
		}
		if(eval("return empty({$sel});")){eval($sel."=[];");}
	}
	
	public function apply($html):string{
		$html=preg_replace('/ @([\w\.\-:]+=)/',' x-on:$1',$html);
		$doc=new \DOMDocument();
		$doc->loadHTML(mb_encode_numericentity($html,[0x80,0xffff,0,0xffff],'UTF-8'),\LIBXML_HTML_NOIMPLIED|\LIBXML_HTML_NODEFDTD|\LIBXML_NOERROR);
		foreach($doc->childNodes??[] as $el){
			$this->_apply($el);
		}
		$html=mb_decode_numericentity($doc->saveHTML(),[0x80,0xffff,0,0xffff],'UTF-8');
		$html=str_replace('<br>','<br/>',$html);
		$html=preg_replace('/><\/(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)>/','/>',$html);
		$html=preg_replace('/ x\-on:([\w\.\-:]+=)/',' @$1',$html);
		return $html;
	}
	private function _apply($el){
		if(!is_a($el,\DOMElement::class)){return;}
		if(empty($el->getAttribute('class'))){
			if(in_array($el->tagName,['br','link','script','source'],true) || empty($this->b)){return;}
			if(!in_array($el->tagName,['template'],true)){
				$el->setAttribute('class','_'.$el->tagName);
			}
		}
		$classes=explode(' ',$el->getAttribute('class')??'');
		$_s=$_b=$_e=false;
		foreach($classes as $i=>$class){
			if(substr($class,-1)==='-'){
				if(strrpos($class,'-',-2)){
					$this->b_stuck[]=[$this->s,$this->b,$this->e];
					$this->b=explode('-',substr($class,0,-1));
					$this->s=[array_shift($this->b)];
					$this->e=[];
					$this->add_selector();
					$_s=$_b=true;
				}
				else{
					$this->b_stuck[]=[$this->s,$this->b,$this->e];
					$this->s=$this->b_stuck[0][0];
					$this->b=[substr($class,0,-1)];
					$this->e=[];
					$this->add_selector();
					$_b=true;
				}
			}
			else if(substr($class,0,1)==='-'){
				$this->b_stuck[]=[$this->s,$this->b,$this->e];
				$this->b[]=substr($class,1);
				$this->e=[];
				$this->add_selector();
				$_b=true;
			}
			else if(substr($class,-1)==='_'){
				$this->e_stuck[]=$this->e;
				$this->e=[substr($class,0,-1)];
				$_e=true;
			}
			else if(substr($class,0,1)==='_'){
				$this->e_stuck[]=$this->e;
				$this->e[]=substr($class,1);
				$_e=true;
			}
			if($_s||$_b||$_e){
				$classes[$i]=$this->get_class();
				$el->setAttribute('class',implode(' ',$classes));
				$this->add_selector();
				break;
			}
		}
		foreach($el->childNodes??[] as $child_el){
			$this->_apply($child_el);
		}
		if($_b){list($this->s,$this->b,$this->e)=array_pop($this->b_stuck);}
		if($_e){$this->e=array_pop($this->e_stuck);}
	}
}