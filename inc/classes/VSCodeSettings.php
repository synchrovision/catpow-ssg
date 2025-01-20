<?php
namespace Catpow;

class VSCodeSettings{
	//settings
	public static function initSettingsData(){
		$data=self::getSettingsData();
		if(empty($data['html.customData']) || !in_array('./_config/customHTMLData.json',$data['html.customData'])){
			$data['html.customData'][]='./_config/customHTMLData.json';
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
		if(!is_dir($dir=dirname($file))){mkdir($dir);}
		file_put_contents($file,json_encode($data,0700));
	}
	private static function getSettingsFile(){
		return ABSPATH.'/.vscode/settings.json';
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
		if(!is_dir($dir=dirname($file))){mkdir($dir);}
		file_put_contents($file,json_encode($data,0700));
	}
	private static function getCustomHTMLDataFile(){
		return CONF_DIR.'/customHTMLData.json';
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
				if(isset($prop_schema['type']) && in_array($prop_schema['type'],['bool','intger','number','string'])){
					$attr=['name'=>$name];
					if(isset($prop_schema['enum'])){
						foreach($prop_schema['enum'] as $val){
							$attr['values'][]=['name'=>$val];
						}
					}
					$data['attributes'][]=$attr;
				}
				else{
					$children[]=$name;
				}
			}
		}
		if(!empty($children)){
			$data['description']="This block should have children:".implode(',',$children);
		}
		return $data;
	}
}