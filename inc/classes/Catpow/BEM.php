<?php
namespace Catpow;
class BEM extends CssRule{
	public $s,$b,$e,$m,$bm,$parent,$b_stuck=[],$m_stuck=[],$selectors=[];
	private function __construct($s=null,$b=null,$e=null,$m=null,$bm=null,$parent=null){
		$this->s=(array)$s;
		$this->b=$b;
		$this->e=(array)$e;
		$this->m=(array)$m;
		$this->bm=(array)$bm;
		$this->parent=$parent;
	}
	public static function block($name){
		if(strpos('-',$name)!==false){
			$s=explode('-',$name);
			$name=array_pop($s);
		}
		else{$s=null;}
		$m=array_filter(explode('_',$name));
		$b=array_shift($m);
		return new self(null,$b,null,$m,$m);
	}
	public function b($name){
		$m=array_filter(explode('_',$name));
		$b=array_shift($m);
		return new self($this->s,$b,null,$m,$m,$this);
	}
	public function get_classes(){
		if(empty($this->b)){return [implode('-',$this->s)];}
		$b_class=(empty($this->s)?'':implode('-',$this->s).'-').$this->b;
		$b_classes=[$b_class];
		foreach($this->bm as $bm){$b_classes[]=$b_class.'_'.$bm;}
		if(empty($this->e)){return $b_classes;}
		$e_classes=[];
		$e_sec='__'.implode('__',$this->e);
		foreach($b_classes as $b_class){
			$e_class=$b_class.$e_sec;
			$e_classes[]=$e_class;
			foreach($this->m as $m){$e_classes[]=$e_class.'_'.$m;}
		}
		return $e_classes;
	}
	public function add_selector($bem=null){
		if(empty($bem)){$bem=$this;}
		$bsel='$this->selectors';
		if(empty($bem->s)){
			$bsel.="['.{$bem->b}']";
		}
		else{
			$bsel.="['.".implode("']['&-",$bem->s)."']['&-{$bem->b}']";
		}
		$esel='';
		foreach($bem->e as $e){
			$esel.="['&__{$e}']";
		}
		$sel=$bsel.$esel;
		if(eval("return empty({$sel});")){eval($sel."=[];");}
		if(!empty($esel)){
			foreach($bem->m as $m){
				$sel=$bsel.$esel."['&_{$m}']";
				if(eval("return empty({$sel});")){eval($sel."=[];");}
			}
		}
		foreach($bem->bm as $bm){
			$sel=$bsel."['&_{$bm}']".$esel;
			if(eval("return empty({$sel});")){eval($sel."=[];");}
			if(!empty($esel)){
				foreach($bem->m as $m){
					$sel=$bsel."['&_{$bm}']".$esel."['&_{$m}']";
					if(eval("return empty({$sel});")){eval($sel."=[];");}
				}
			}
		}
	}
	public function __get($name){
		if($name==='§'){array_pop($this->s);$this->b=false;$this->e=$this->m=$this->bm=[];return;}
		if($name==='»'){list($this->s,$this->b,$this->e,$this->m,$this->bm)=array_pop($this->b_stuck);return;}
		if($name==='_'){array_pop($this->e);$this->m=array_pop($this->m_stuck);return;}
		if(mb_substr($name,0,1)==='§'){
			$this->s[]=mb_substr($name,1);
			$this->b=false;$this->e=$this->m=$this->bm=[];
			return $this;
		}
		if(mb_substr($name,0,1)==='«'){
			$m=array_filter(explode('_',mb_substr($name,1)));
			$b=array_shift($m);
			if(strpos('-',$b)!==false){
				$s=array_merge((array)$this->s,explode('-',$b));
				$b=array_pop($s);
			}
			else{$s=$this->s;}
			$this->b_stuck[]=[$this->s,$this->b,$this->e,$this->m,$this->bm];
			list($this->s,$this->b,$this->e,$this->m,$this->bm)=[$s,$b,[],$m,$m];
			$this->add_selector();
			return $this;
		}
		if(substr($name,-1)==='_'){
			$m=array_filter(explode('_',substr($name,0,-1)));
			$e=array_shift($m);
			$this->m_stuck[]=$this->m;
			$this->e[]=$e;
			$this->m=$m;
			$this->add_selector();
			return $this;
		}
		$m=array_filter(explode('_',$name));
		$e=array_shift($m);
		$bem=new self($this->s,$this->b,array_merge($this->e,[$e]),$m,$this->bm,$this);
		$this->add_selector($bem);
		return $bem;
	}
	public function __toString(){
		return implode(' ',$this->get_classes());
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
			$el->setAttribute('class','_'.$el->tagName);
		}
		$classes=explode(' ',$el->getAttribute('class')??'');
		$_s=$_b=$_e=false;
		foreach($classes as $i=>$class){
			if(substr($class,-1)==='-'){
				$this->s[]=substr($class,0,-1);
				$this->b=false;$this->e=$this->m=$this->bm=[];
				$_s=true;
			}
			if(substr($class,-1)==='_'){
				$m=array_filter(explode('_',mb_substr($class,0,-1)));
				$b=array_shift($m);
				if(strpos('-',$b)!==false){
					$s=array_merge((array)$this->s,explode('-',$b));
					$b=array_pop($s);
				}
				else{$s=$this->s;}
				$this->b_stuck[]=[$this->s,$this->b,$this->e,$this->m,$this->bm];
				list($this->s,$this->b,$this->e,$this->m,$this->bm)=[$s,$b,[],$m,$m];
				$this->add_selector();
				$_b=true;
			}
			if(substr($class,0,1)==='_'){
				$m=array_filter(explode('_',substr($class,1)));
				$e=array_shift($m);
				$this->m_stuck[]=$this->m;
				$this->e[]=$e;
				$this->m=$m;
				$this->add_selector();
				$_e=true;
			}
			if($_s||$_b||$_e){
				$classes[$i]=$this.'';
				$el->setAttribute('class',implode(' ',$classes));
				break;
			}
		}
		foreach($el->childNodes??[] as $child_el){
			$this->_apply($child_el);
		}
		if($_s){array_pop($this->s);$this->b=false;$this->e=$this->m=$this->bm=[];}
		if($_b){list($this->s,$this->b,$this->e,$this->m,$this->bm)=array_pop($this->b_stuck);}
		if($_e){array_pop($this->e);$this->m=array_pop($this->m_stuck);}
	}
}