<?php
namespace Catpow\JsonLD;
use Catpow\Site;

class BreadcrumbList extends JsonLD{
	public static function get_data($page){
		$site=Site::get_instance();
		$item_list=[];
		foreach($page->ancestors as $i=>$ansector){
			$item_list[]=[
				"@type"=> "ListItem",
				"position"=> $i+1,
				"name"=> $ansector->title,
				"item"=> rtrim($site->url,'/').'/'.$ansector->uri,
			];
		}
		return [
			'@type'=>'BreadcrumbList',
			'itemListElement'=>$item_list
		];
	}
}