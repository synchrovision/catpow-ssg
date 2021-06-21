<?php
namespace Catpow;
class Deps{
	public static $regsitered=[
		'js'=>[
			'react'=>[
				'set'=>[
					['src'=>'https://unpkg.com/react@17/umd/react.development.js','attr'=>'crossorigin'],
					['src'=>'https://unpkg.com/react-dom@17/umd/react-dom.development.js','attr'=>'crossorigin']
				]
			],
			'alpine'=>[
				'set'=>[
					['src'=>'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js','attr'=>'type="module"'],
					['src'=>'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine-ie11.min.js','attr'=>'nomodule defer'],
					['src'=>'ttps://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.0.0/dist/alpine-ie11.js','attr'=>'defer'],
				]
			],
			'axios'=>['src'=>'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js'],
			'jquery'=>['src'=>'https:////ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js']
		],
		'css'=>[
			
		]
	];
	public $enqueued=[],$rendered=[];
	public function __construct($type){
		$this->type=$type;
	}
	public function enqueue($handler,$src=null,$deps=[]){
		if(isset($this->enqueued[$handler])){return;}
		if(isset(self::$regsitered[$this->type][$handler])){
			$this->enqueued[$handler]=self::$regsitered[$this->type][$handler];
			return;
		}
		if(!isset($src)){$src=$handler;}
		$this->enqueued[$handler]=compact('src','deps');
	}
	public function register($handler,$src,$deps=[]){
		self::$regsitered[$this->type][$handler]=compact('src','deps');
	}
	public function render($handlers=null){
		if(empty($handlers)){
			$handlers=array_filter(array_keys($this->enqueued),function($handler){return empty($this->rendered[$handler]);});
			usort($handlers,function($a,$b){
				if(empty($this->enqueued[$a]['deps']) && empty($this->enqueued[$b]['deps'])){return 0;}
				if(in_array($a,$this->enqueued[$b]['deps'],1)){return -1;}
				if(in_array($b,$this->enqueued[$a]['deps'],1)){return 1;}
				return 0;
			});
		}
		foreach($handlers as $handler){$this->render_tag($handler);}
	}
	public function render_tag($handler){
		$source=$this->enqueued[$handler]??self::$regsitered[$this->type][$handler]??null;
		if(empty($source)){return false;}
		switch($this->type){
			case 'js':
				if(!empty($source['src'])){printf('<script src="%s" %s></script>',$source['src'],$source['attr']??'');}
				if(!empty($source['set'])){
					foreach($source['set'] as $item){
						printf('<script src="%s" %s></script>',$item['src'],$item['attr']??'');
					}
				}
				break;
			case 'css':
				printf('<link rel="stylesheet" type="text/css" href="%s" %s/>',$source['src'],$source['attr']??'');
				break;
		}
		$this->rendered[$handler]=true;
		return true;
	}
}