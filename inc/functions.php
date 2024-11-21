<?php
namespace Catpow;

function _d($data){
	Debug::dump($data);
}

function picture($name,$alt,$className=null,$attr=null,$bp=null){
	global $page;
	if(empty($bp)){$bp=['sp'=>-767,'tb'=>-1024,'lt'=>-1600,'fhd'=>-1920,'hd'=>-1280,'xga'=>-1024,'vga'=>-640];}
	preg_match('/^(?P<name>.+)(?P<ext>\.\w+)$/',$name,$matches);
	$rtn=sprintf('<picture%s>',HTML::get_attr_code(array_merge(['class'=>$className],(array)$attr)));
	foreach($bp as $media=>$mq){
		if(is_numeric($mq)){
			$mq=($mq>0)?"min-width:{$mq}px":('max-width:'.abs($mq).'px');
		}
		if(
			(strpos($name,'_pc_')!==false && $file=$page->get_the_file($src=str_replace('_pc_',"_{$media}_",$name))) ||
			(strpos($name,'_pc.')!==false && $file=$page->get_the_file($src=str_replace('_pc.',"_{$media}.",$name))) ||
			$file=$page->get_the_file($src=sprintf('%s_%s%s',$matches['name'],$media,$matches['ext']))
		){
			if(!empty($webp=$page->generate_webp_for_image($src))){
				$rtn.=sprintf('<source media="(%s)" srcset="%s" type="image/webp"/>',$mq,$webp);
			}
			$rtn.=sprintf('<source media="(%s)" srcset="%s"/>',$mq,$src,mime_content_type($file));
			$has_alt_image=true;
		}
	}
	$file=$page->get_the_tmpl_file($name);
	$has_tmpl_image=!empty($file);
	if(!$has_tmpl_image){$file=$page->get_the_file($name);}
	if(empty($file)){
		$rtn.=sprintf('<img src="%s" alt="%s" width="%d" height="%d"/>',$name,$alt,100,100);
	}
	else{
		$mime=mime_content_type($file);
		$size=getimagesize($file);
		if($size){
			if(empty($has_alt_image)){
				foreach(['s'=>200,'m'=>300,'l'=>400] as $s=>$u){
					if(function_exists('imagewebp') && $size[0]>$u*4){
						$src=sprintf('%s_%s.webp',$matches['name'],$s);
						$dest_file=$page->get_file_path_for_uri($src);
						if(!file_exists($dest_file) || filemtime($file)>filemtime($dest_file)){
							imagewebp(imagescale($page->get_gd($name),$u*3),$dest_file);
						}
						$rtn.=sprintf('<source media="(max-width:%dpx)" srcset="%s?%d" type="image/webp"/>',$u*2,$src,filemtime($file));
					}
				}
			}
			if(!empty($webp=$page->generate_webp_for_image($name))){
				$rtn.=sprintf('<source srcset="%s" type="image/webp"/>',$webp);
			}
			$rtn.=sprintf('<img src="%s?%d" alt="%s" width="%d" height="%d"/>',$name,filemtime($file),$alt,$size[0],$size[1]);
		}
		else{
			$rtn.=sprintf('<img src="%s?%d" alt="%s"/>',$name,filemtime($file),$alt);
		}
	}
	$rtn.='</picture>';
	return $rtn;
}
function table($data,$props=null){
	if(is_string($data)){$data=csv($data)->data;}
	if(empty($props)){$props=[];}
	if(is_string($props)){$props=['class'=>$props];}
	$rtn=sprintf('<table%s>',HTML::get_attr_code(['class'=>$props['classes']['table']??$props['class']??null]));
	$hr=$props['hr']??1;
	$hc=$props['hc']??0;
	$atts=$props['atts']??[];
	if(!empty($props['caption'])){
		$rtn.=sprintf('<caption%s>%s</caption>',HTML::get_attr_code(['class'=>$props['classes']['caption']??null]),$props['caption']);
	}
	if(!empty($props['colgroup'])){
		$rtn.=sprintf('<colgroup%s>',HTML::get_attr_code(['class'=>$props['classes']['colgroup']??'']));
		$c=0;
		foreach($props['colgroup'] as $i=>$col){
			$col['class']=$props['classes']['col']??'';
			$rtn.=sprintf('<col%s/>',HTML::get_attr_code($col,compact('c','i')));
			$c+=$col['span']??1;
		}
		$rtn.='</colgroup>';
	}
	foreach($data as $r=>$row){
		foreach($row as $c=>$cell){
			if(is_null($cell)){continue;}
			$tag=($r<$hr || $c<$hc)?'th':'td';
			$attr=['tag'=>$tag];
			if(($data[$r][$c+1]??'')==='<'){
				$s=1;
				while(($data[$r][$c+$s]??'')==='<'){$data[$r][$c+$s]=null;$s++;}
				$attr['colspan']=$s;
			}
			if(($data[$r+1][$c]??'')==='^'){
				$s=1;
				while(($data[$r+$s][$c]??'')==='^'){$data[$r+$s][$c]=null;$s++;}
				$attr['rowspan']=$s;
				if(!empty($attr['colspan'])){
					for($rs=$attr['rowspan']-1;$rs<0;$rs--){
						for($cs=$attr['colspan']-1;$cs<0;$cs--){
							$data[$r+$rs][$c+$cs]=null;
						}
					}
				}
			}
			$attr['class']=$props['classes'][$tag]??'';
			if(substr($cell,0,2)==='$ '){
				if(strpos($cell,"\n")){
					$tag_data=strstr($cell,"\n",true);
					$data[$r][$c]=substr(strstr($cell,"\n"),1);
				}
				else{
					$tag_data=$cell;
					$data[$r][$c]='';
				}
				$attr=array_merge($attr,HTML::parse_tag_data(substr($tag_data,2)));
			}
			$atts[$r][$c]=array_merge($attr,$atts[$r][$c]??[]);
		}
	}
	$cb=array_key_exists('cb',$props)?
		(empty($props['cb'])?function($str){return $str;}:$props['cb']):
		function($str){return nl2br(rtf($str));};
	$r=0;
	if(!empty($hr)){
		$rtn.=sprintf('<thead%s>',HTML::get_attr_code(['class'=>$props['classes']['thead']??'']));
		for(;$r<$hr;$r++){
			$rtn.=sprintf('<tr%s>',HTML::get_attr_code(['class'=>$props['classes']['tr']??''],compact('r')));
			foreach($data[$r] as $c=>$cell){
				if(is_null($cell)){continue;}
				$rtn.=sprintf('<%s%s>%s</%1$s>',$atts[$r][$c]['tag'],HTML::get_attr_code($atts[$r][$c],compact('r','c','cell')),$cb($cell,$r,$c));
			}
			$rtn.='</tr>';
		}
		$rtn.='</thead>';
	}
	$rtn.=sprintf('<tbody%s>',HTML::get_attr_code(['class'=>$props['classes']['tbody']??'']));
	for($l=count($data);$r<$l;$r++){
		$rtn.=sprintf('<tr%s>',HTML::get_attr_code(['class'=>$props['classes']['tr']??''],compact('r')));
		foreach($data[$r] as $c=>$cell){
			if(is_null($cell)){continue;}
			$rtn.=sprintf('<%s%s>%s</%1$s>',$atts[$r][$c]['tag'],HTML::get_attr_code($atts[$r][$c],compact('r','c')),$cb($cell,$r,$c));
		}
		$rtn.='</tr>';
	}
	$rtn.='</tbody></table>';
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
	if(isset($cache[$file])){return $cache[$file];}
	if(!file_exists($file)){return $cache[$file]=[];}
	$data=[];
	$entries=array_chunk(preg_split('/\n*^\[(.+?)\]\n/m',file_get_contents($file),-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE),2);
	foreach($entries as list($key,$value)){
		$data[$key]=$value;
	}
	return $cache[$file]=$data;
}
function nl2wbr($str){
	return str_replace("\n",'<wbr/>',str_replace("\n\n",'<br/>',$str));
}
function md($text,$class=null){
	if(is_null($text)){return '';}
	if(substr($text,-3)==='.md'){
		global $page;
		$text=file_get_contents($page->get_the_file($text));
	}
	return sprintf('<div class="%s-">%s</div>',$class?:MarkDown::$default_class,MarkDown::do_markdown($text));
}
function simple_md($text,$classes=[]){
	$classes=array_merge(
		['a'=>'_link','img'=>'_image'],
		$classes
	);
	$text=preg_replace('/!\[(.+?)\]\((.+?)\)/','<img class="'.$classes['img'].'" src="$2" alt="$1"/>',$text);
	$text=preg_replace('/\[(.+?)\]\((.+?)\)/','<a class="'.$classes['a'].'" href="$2" target="_brank">$1</a>',$text);
	return $text;
}
function rtf($text,$pref=null){
	return RTF::replace($text,$pref);
}
function rxf($text,$pref=null){
	return RXF\RXF::replace($text,$pref);
}
function do_shortcode($str){
	return ShortCode::do_shortcode($str);
}
function add_shortcode($name,$function){
	return ShortCode::add_shortcode($name,$function);
}

function csv($csv,$flags=CSV::CAST_NUMERIC|CSV::CAST_BOOL){
	global $page;
	if(substr($csv,-4)!=='.csv'){$csv='csv/'.$csv.'.csv';}
	if(!empty($page)){
		return new CSV($page->get_the_file($csv),$flags);
	}
	if(file_exists($f=ABSPATH.'/'.$csv) || file_exists($f=TMPL_DIR.'/'.$csv) || file_exists($f=CONF_DIR.'/'.$csv) || file_exists($f=INC_DIR.'/'.$csv)){
		return new CSV($f,$flags);
	}
	return false;
}
function json($json){
	global $page;
	if(substr($json,-5)!=='.json'){$json='json/'.$json.'.json';}
	if(!empty($page)){
		return json_decode(file_get_contents($page->get_the_file($json)),true);
	}
	if(file_exists($f=ABSPATH.'/'.$json) || file_exists($f=TMPL_DIR.'/'.$json) || file_exists($f=CONF_DIR.'/'.$json) || file_exists($f=INC_DIR.'/'.$json)){
		return json_decode(file_get_contents($f),true);
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

function svg($props,$children=[]){
	$svg=new SVG\SVG($props,$children);
	ob_start();
	$svg->render();
	return ob_get_clean();
}