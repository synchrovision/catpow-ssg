<?php
namespace Catpow;

class VSCodeSettings{
	const
		SETTINGS_JSON_FILE='.vscode/settings.json',
		EMMET_EXTENTIONS_PATH='_config/emmet',
		CUSTOM_HTML_DATA_FILE='_config/customHTMLData.json';

	//settings
	public static function initSettingsData(){
		$data=self::getSettingsData();
		if(empty($data['html.customData']) || !in_array(self::CUSTOM_HTML_DATA_FILE,$data['html.customData'])){
			$data['html.customData'][]=self::CUSTOM_HTML_DATA_FILE;
		}
		if(empty($data['emmet.includeLanguages']['php'])){
			$data['emmet.includeLanguages']['php']='html';
		}
		if(empty($data['emmet.extensionsPath']) || !in_array(self::EMMET_EXTENTIONS_PATH,$data['emmet.extensionsPath'])){
			$data['emmet.extensionsPath'][]=self::EMMET_EXTENTIONS_PATH;
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
		return ABSPATH.'/'.self::SETTINGS_JSON_FILE;
	}
	//emmet
	public static function initEmmetSnippets(){
		$data=self::getEmmetSnippets();
		$data['html']['snippets']=array_merge($data['html']['snippets']??[],self::getEmmetSnippetsOfBlocks());
		self::setEmmetSnippets($data);
	}
	public static function getEmmetSnippets(){
		$file=self::geEmmetSnippetsFile();
		if(!file_exists($file)){return [];}
		return json_decode(file_get_contents($file),true);
	}
	public static function setEmmetSnippets($data){
		$file=self::geEmmetSnippetsFile();
		if(!is_dir($dir=dirname($file))){mkdir($dir,0755,true);}
		file_put_contents($file,json_encode($data,0700));
	}
	private static function geEmmetSnippetsFile(){
		return ABSPATH.'/'.self::EMMET_EXTENTIONS_PATH.'/snippets.json';
	}
	public static function getEmmetSnippetsOfBlocks(){
		$datas=[];
		foreach(glob(TMPL_DIR.'/blocks/*/schema.json') as $schema_file){
			$dir=dirname($schema_file);
			$tag='block-'.basename($dir);
			$datas[$tag]=self::getEmmetCodeFromSchema($tag,json_decode(file_get_contents($schema_file),true));
		}
		return $datas;
	}
	private static function getEmmetCodeFromSchema($tag,$schema,$ctx=null){
		$code=$tag;
		if(empty($ctx)){$ctx=(object)['count'=>1];}
		if(isset($schema['properties'])){
			$children=[];
			foreach($schema['properties'] as $name=>$prop_schema){
				if(isset($prop_schema['items'])){continue;}
				if(isset($prop_schema['type']) && in_array($prop_schema['type'],['boolean','intger','number','string'])){
					if($prop_schema['type']==='boolean'){
						$code.=sprintf('${%d:[%s]}',$ctx->count++,$name);
					}
					else{
						$val=$prop_schema['default']??$prop_schema['enum'][0]??null;
						$code.=sprintf('[%s="${%d%s%s}"]',$name,$ctx->count++,isset($val)?':':'',$val);
					}
				}
				else{
					$children[$name]=$prop_schema;
				}
			}
			if(!empty($children)){
				foreach($children as $name=>$child){
					$children[$name]=self::getEmmetCodeFromSchema($name,$child,$ctx);
				}
				$code=sprintf('(%s>%s)',$code,implode('+',array_values($children)));
			}
			else{
				$val=$schema['default']??$schema['enum'][0]??null;
				$code.=sprintf('{${%d%s%s}}',$ctx->count++,isset($val)?':':'',$val);
			}
		}
		else{
			$val=$schema['default']??$schema['enum'][0]??null;
			$code.=sprintf('{${%d%s%s}}',$ctx->count++,isset($val)?':':'',$val);
		}
		return $code;
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
		return ABSPATH.'/'.self::CUSTOM_HTML_DATA_FILE;
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
				$data=array_merge($data,self::getHTMLDataOfBlock($schema));
				if(isset($schema['description'])){$data['description']=$schema['description'];}
			}
			$datas[]=$data;
		}
		return $datas;
	}
	private static function getHTMLDataOfBlock($schema){
		$data=[];
		$children=[];
		if(isset($schema['properties'])){
			foreach($schema['properties'] as $name=>$prop_schema){
				if(isset($prop_schema['items'])){continue;}
				if(isset($prop_schema['type'])){
					if(in_array($prop_schema['type'],['boolean','intger','number','string'])){
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