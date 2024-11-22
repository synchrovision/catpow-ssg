<?php
namespace Catpow;
class BEM extends CssRule{
	public $s,$b,$e,$m,$bm,$parent,$b_stuck=[],$m_stuck=[],$e_stuck=[],$selectors=[];
	protected function __construct($s=null,$b=null,$e=null,$m=null,$bm=null,$parent=null){
		$this->s=(array)$s;
		$this->b=(array)$b;
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
	public function get_base_class(){
		$class='';
		if(empty($this->s)){
			if(empty($this->b)){return [];}
			$class=implode('-',$this->b);
		}
		else{
			$class=implode('-',$this->s);
			if(!empty($this->b)){
				$class.='-'.implode('-',$this->b);
			}
		}
		if(!empty($this->e)){
			$class.='__'.implode('-',$this->e);
		}
		return $class;
	}
	public function get_classes(){
		$class=$this->get_base_class();
		$classes=[$class];
		foreach($this->bm as $bm){$classes[]=$class.'--'.$bm;}
		foreach($this->m as $m){$classes[]=$class.'--'.$m;}
		return $classes;
	}
	public function add_selector($bem=null){
		if(empty($bem)){$bem=$this;}
		$bsel='$this->selectors';
		if(empty($bem->s)){
			$bsel.="['.".(implode("']['&-",$this->b))."']";
		}
		else{
			$bsel.="['.".implode("']['&-",$bem->s)."']";
			$bsel.="['&-".(implode("']['&-",$this->b))."']";
			if(eval("return empty({$bsel});")){eval($bsel."=[];");}
		}
		$esel='';
		if(!empty($bem->e)){
			$esel="['&__".(implode("']['&-",$bem->e))."']";
		}
		$sel=$bsel.$esel;
		if(eval("return empty({$sel});")){eval($sel."=[];");}
		foreach($bem->m as $m){
			$msel=$sel."['&--{$m}']";
			if(eval("return empty({$msel});")){eval($msel."=[];");}
		}
		foreach($bem->bm as $bm){
			$msel=$sel."['&--{$bm}']";
			if(eval("return empty({$msel});")){eval($msel."=[];");}
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
			if(in_array($el->tagName,['br','link','script','source','template'],true)){return;}
			$el->setAttribute('class','_'.$el->tagName);
		}
		$classes=explode(' ',$el->getAttribute('class')??'');
		$_b=$_e=false;
		foreach($classes as $i=>$class){
			if(substr($class,-1)==='-'){
				//blockt
				$this->b_stuck[]=[$this->b,$this->e,$this->m,$this->bm,$this->e_stuck];
				$this->b=explode('-',substr($class,0,-1));
				$this->e=$this->m=$this->bm=[];
				$_b=true;
			}
			elseif(substr($class,0,1)==='-'){
				//sub block
				$this->b_stuck[]=[$this->b,$this->e,$this->m,$this->bm,$this->e_stuck];
				$this->b=array_merge($this->b,explode('-',substr($class,1)));
				$this->e=$this->m=$this->bm=[];
				$_b=true;
			}
			elseif(substr($class,-1)==='_'){
				//element
				$this->e_stuck[]=[$this->e,$this->m];
				$this->e=explode('-',substr($class,0,-1));
				$this->m=[];
				$_e=true;
			}
			elseif(substr($class,0,1)==='_'){
				//sub element
				$this->e_stuck[]=[$this->e,$this->m];
				$this->e=array_merge($this->e,explode('-',substr($class,1)));
				$this->m=[];
				$_e=true;
			}
			if($_b||$_e){
				foreach($classes as $j=>$class){
					if(substr($class,0,2)==='--'){
						//modifier
						$classes[$j]=null;
						if($_b){$this->bm[]=substr($class,2);}
						else{$this->m[]=substr($class,2);}
					}
				}
				$classes[$i]=$this.'';
				$this->add_selector();
				$el->setAttribute('class',implode(' ',$classes));
				break;
			}
		}
		foreach($el->childNodes??[] as $child_el){
			$this->_apply($child_el);
		}
		if($_b){list($this->b,$this->e,$this->m,$this->bm,$this->e_stuck)=array_pop($this->b_stuck);}
		if($_e){list($this->e,$this->m)=array_pop($this->e_stuck);}
	}
}