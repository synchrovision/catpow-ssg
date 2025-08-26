<?php
namespace Catpow;

class VSCodeSettings{
	const
		SETTINGS_JSON_FILE='.vscode/settings.json',
		CUSTOM_HTML_DATA_FILE='.vscode/block-tags.json',
		BLOCK_SNIPPETS_FILE='.vscode/block.code-snippets';

	//settings
	public static function initSettingsData(){
		$data=self::getSettingsData();
		if(empty($data['html.customData']) || !in_array(self::CUSTOM_HTML_DATA_FILE,$data['html.customData'])){
			$data['html.customData'][]=self::CUSTOM_HTML_DATA_FILE;
		}
		self::setSettingsData($data);
	}
	public static function getSettingsData(){
		$file=self::getSettingsFile();
		if(!file_exists($file)){return [];}
		return json_decode(file_get_contents($file),true);
	}
	public static function setSettingsData($data){
		$file=self::getSettingsFile();
		if(!is_dir($dir=dirname($file))){mkdir($dir,0755,true);}
		file_put_contents($file,json_encode($data,0700));
	}
	private static function getSettingsFile(){
		return ROOT_DIR.'/'.self::SETTINGS_JSON_FILE;
	}
	//snippets
	public static function initSnippets(){
		self::setSnippets(array_merge(self::getSnippets(),self::getSnippetsOfBlocks()));
	}
	public static function getSnippets(){
		$file=self::getSnippetsFile();
		if(!file_exists($file)){return [];}
		return json_decode(file_get_contents($file),true);
	}
	public static function setSnippets($data){
		$file=self::getSnippetsFile();
		if(!is_dir($dir=dirname($file))){mkdir($dir,0755,true);}
		file_put_contents($file,json_encode($data,0700));
	}
	private static function getSnippetsFile(){
		return ROOT_DIR.'/'.self::BLOCK_SNIPPETS_FILE;
	}
	public static function getSnippetsOfBlocks(){
		$datas=[];
		foreach(glob(TMPL_DIR.'/blocks/*/schema.json') as $schema_file){
			$dir=dirname($schema_file);
			$tag='block-'.basename($dir);
			$datas[$tag]=[
				"scope"=>"html",
				'prefix'=>$tag,
				'body'=>self::getSnippetBodyFromSchema($tag,json_decode(file_get_contents($schema_file),true))
			];
		}
		return $datas;
	}
	private static function getSnippetBodyFromSchema($tag,$schema,$level=0,$ctx=null){
		if(empty($ctx)){$ctx=(object)['count'=>1];}
		$lines=[];
		$atts='';
		$children=[];
		$indent=str_repeat("\t",$level);
		if(isset($schema['properties'])){
			foreach($schema['properties'] as $name=>$prop_schema){
				if(isset($prop_schema['items'])){continue;}
				if(isset($prop_schema['type']) && in_array($prop_schema['type'],['boolean','integer','number','string'])){
					if($prop_schema['type']==='boolean'){
						$atts.=sprintf(' ${%d:%s}',$ctx->count++,$name);
					}
					else{
						$atts.=sprintf(' %s="%s"',$name,self::getSnippetPlaceholderFromSchema($prop_schema,$ctx));
					}
				}
				else{
					$children[$name]=$prop_schema;
				}
			}
		}
		if(!empty($children)){
			$lines[]=sprintf('%s<%s%s>',$indent,$tag,$atts);
			foreach($children as $name=>$child){
				$lines=array_merge($lines,self::getSnippetBodyFromSchema($name,$child,$level+1,$ctx));
			}
			$lines[]=sprintf('%s</%s>',$indent,$tag);
		}
		else{
			$lines[]=sprintf('%s<%s%s>%s</%2$s>',$indent,$tag,$atts,self::getSnippetPlaceholderFromSchema($schema,$ctx));
		}
		return $lines;
	}
	private static function getSnippetPlaceholderFromSchema($schema,$ctx){
		if(isset($schema['enum'])){return sprintf('${%d|%s|}',$ctx->count++,implode(',',$schema['enum']));}
		if(isset($schema['pattern'])){
			if(preg_match('/^\^([\w\s\-,#:;]*(\([#\w\-\|\\\.\+\*\{\},]+\)[\w\s\-,#:;]*)+)\$$/',$schema['pattern'],$matches)){
				return preg_replace_callback('/\(([#\w\-\|\\\.\+\*\{\},]+)\)/',function($matches)use($ctx){
					if(preg_match('/[\\\.\+\*\{\},]/',$matches[1])){return sprintf('${%d}',$ctx->count++);}
					return sprintf('${%d|%s|}',$ctx->count++,str_replace('|',',',$matches[1]));
				},$matches[1]);
			}
		}
		if(isset($schema['default'])){return sprintf('${%d:%s}',$ctx->count++,$schema['default']);}
		return sprintf('${%d}',$ctx->count++);
	}
	//customHTMLData
	public static function initCustomHTMLData(){
		$data=self::getCustomHTMLData();
		$data['tags']=self::getHTMLDataOfBlocks();
		self::setCustomHTMLData($data);
	}
	public static function getCustomHTMLData(){
		$file=self::getCustomHTMLDataFile();
		if(!file_exists($file)){return [];}
		return json_decode(file_get_contents($file),true);
	}
	public static function setCustomHTMLData($data){
		$file=self::getCustomHTMLDataFile();
		if(!is_dir($dir=dirname($file))){mkdir($dir,0755,true);}
		file_put_contents($file,json_encode($data,0700));
	}
	private static function getCustomHTMLDataFile(){
		return ROOT_DIR.'/'.self::CUSTOM_HTML_DATA_FILE;
	}
	//blockHTMLData
	public static function getHTMLDataOfBlocks(){
		$datas=[];
		foreach(glob(TMPL_DIR.'/blocks/*/block.php') as $block_file){
			$dir=dirname($block_file);
			$tag='block-'.basename($dir);
			$data=['name'=>$tag];
			if(file_exists($schema_file=$dir.'/schema.json')){
				$schema=json_decode(file_get_contents($schema_file),true);
				$data=array_merge($data,self::getHTMLDataFromSchema($schema));
				if(isset($schema['description'])){$data['description']=$schema['description'];}
			}
			$datas[]=$data;
		}
		return $datas;
	}
	private static function getHTMLDataFromSchema($schema){
		$data=[];
		$children=[];
		if(isset($schema['properties'])){
			foreach($schema['properties'] as $name=>$prop_schema){
				if(isset($prop_schema['items'])){continue;}
				if(isset($prop_schema['type'])){
					if(in_array($prop_schema['type'],['boolean','integer','number','string'])){
						$attr=['name'=>$name];
						if(isset($prop_schema['enum'])){
							foreach($prop_schema['enum'] as $val){
								$attr['values'][]=['name'=>$val];
							}
						}
						$data['attributes'][]=$attr;
					}
				}
			}
		}
		return $data;
	}
}