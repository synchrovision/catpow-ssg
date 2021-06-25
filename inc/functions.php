<?php
namespace Catpow;

function _d($data){
	Debug::dump($data);
}

function picture($name,$alt,$ext='png'){
	printf('<picture><source media="(max-width: 767px)" srcset="%1$s_sp.%3$s"><img src="%1$s.%3$s" alt="%2$s"/></picture>',$name,$alt,$ext);
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
	if(substr($csv,-4)!=='.csv'){$csv='/csv/'.$csv.'.csv';}
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