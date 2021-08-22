<?php
namespace Catpow;

function _d($data){
	Debug::dump($data);
}

function picture($name,$alt,$bp=null){
	if(empty($bp)){$bp=['sp'=>-767];}
	preg_match('/^(?P<name>.+)(?P<ext>\.\w+)$/',$name,$matches);
	$rtn='<picture>';
	foreach($bp as $media=>$mq){
		if(is_numeric($mq)){
			$mq=($mq>0)?('min-width:'.$mq):('max-width:'.abs($mq));
		}
		$rtn.=sprintf('<source media="(max-width: 767px)" srcset="%s_%s%s">',$matches['name'],$media,$matches['ext']);
	}
	$rtn.=sprintf('<img src="%s" alt="%s"/></picture>',$name,$alt);
	return $rtn;

}
function texts($file='texts'){
	global $page;
	static $cache=[];
	$file.='.txt';
	if(!empty($page)){
		$file=$page->get_the_file($file);
	}
	elseif(file_exists($f=ABSPATH.$file) || file_exists($f=TMPL_DIR.$file)|| file_exists($f=INC_DIR.$file)){
		$file=$f;
	}
	if(isset($cache[$file])){return $cach[$file];}
	if(!file_exists($file)){return $cache[$file]=[];}
	$data=[];
	$entries=array_chunk(preg_split('/\n*^\[(.+?)\]\n/m',file_get_contents($file),-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE),2);
	foreach($entries as list($key,$value)){
		$data[$key]=$value;
	}
	return $cach[$file]=$data;
}
function md($text){
	if(is_null($text)){return '';}
	if(substr($text,-3)==='.md'){
		global $page;
		$text=file_get_contents($page->get_the_file($text));
	}
	return \Michelf\MarkdownExtra::defaultTransform(ShortCode::do_shortcode($text));
}
function simple_md($text,$param=[]){
	$param=array_merge(
		['link_class'=>'link','image_class'=>'image'],
		$param
	);
	$text=preg_replace('/!\[(.+?)\]\((.+?)\)/','<img class="'.$param['image_class'].'" src="$2" alt="$1"/>',$text);
	$text=preg_replace('/\[(.+?)\]\((.+?)\)/','<a class="'.$param['link_class'].'" href="$2" target="_brank">$1</a>',$text);
	return $text;
}
function do_shortcode($str){
	return ShortCode::do_shortcode($str);
}
function add_shortcode($name,$function){
	return ShortCode::add_shortcode($name,$function);
}

function csv($csv){
	global $page;
	if(substr($csv,-4)!=='.csv'){$csv='/csv/'.$csv.'.csv';}
	if(!empty($page)){
		return new CSV($page->get_the_file($csv));
	}
	if(file_exists($f=ABSPATH.$csv) || file_exists($f=TMPL_DIR.$csv)|| file_exists($f=INC_DIR.$csv)){
		return new CSV($f);
	}
	return false;
}

function enqueue_style($handler,$src=null,$deps=[]){
	global $page;
	$page->styles->enqueue($handler,$src,$deps);
}
function enqueue_script($handler,$src=null,$deps=[]){
	global $page;
	$page->scripts->enqueue($handler,$src,$deps);
}

function get_template_part($name,$slug=null){
	if(!empty($slug)){
		if(file_exists($f=TMPL_DIR.'/'.$name.'-'.$slug.'.php')){
			return include $f;
		}
	}
	return include TMPL_DIR.'/'.$name.'.php';
}
function get_header($slug=null){
	get_template_part('header',$slug);
}
function get_sidebar($slug=null){
	get_template_part('sidebar',$slug);
}
function get_footer($slug=null){
	get_template_part('footer',$slug);
}

function block($block,$props=[],$children=[]){
	$block_obj=new Block($block,$props,$children);
	$block_obj->init();
	return $block_obj->get_html();
}
function contents($contents,$vars=[],$children=[]){
	global $page;
	extract($vars);
	$children=is_array($children)?implode("\n",iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($children)),false)):$children;
	if(is_a($children,\Closure::class)){
		ob_start();
		$children($vars);
		$children=ob_get_clean();
	}
	ob_start();
	include $page->get_the_file('contents/'.$contents.'.php');
	return ob_get_clean();
}