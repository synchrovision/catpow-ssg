<?php
namespace Catpow;
class Deps{
	public $type;
	public static $regsitered=[
		'js'=>[
			'react-dev'=>[
				'set'=>[
					['src'=>'https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.development.min.js','attr'=>'crossorigin'],
					['src'=>'https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.development.min.js','attr'=>'crossorigin']
				]
			],
			'react'=>[
				'set'=>[
					['src'=>'https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js','attr'=>'crossorigin'],
					['src'=>'https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js','attr'=>'crossorigin']
				]
			],
			'alpine'=>['src'=>'https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.3.0/alpine.js','attr'=>'defer'],
			'alpine2'=>[
				'set'=>[
					['src'=>'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js','attr'=>'type="module"'],
					['src'=>'https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine-ie11.min.js','attr'=>'nomodule defer']
				]
			],
			'axios'=>['src'=>'https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js'],
			'cookie'=>['src'=>'https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js'],
			'hammerjs'=>['src'=>'https://hammerjs.github.io/dist/hammer.min.js'],
			'tinygesture'=>['src'=>'https://cdn.jsdelivr.net/npm/tinygesture','attr'=>'type="module"'],
			'urljs'=>['src'=>'https://cdnjs.cloudflare.com/ajax/libs/urljs/2.6.2/url.min.js'],
			'jquery'=>['src'=>'https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js'],
			'catpow-animate'=>['src'=>'/js/catpow_animate.js','deps'=>['jquery']],
			'slick'=>['src'=>'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js','deps'=>['jquery']],
			'bootstrap'=>[
				'src'=>'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/js/bootstrap.bundle.min.js',
				'attr'=>'integrity="sha384-ygbV9kiqUc6oa4msXn9868pTtWMgiQaeYH7/t7LECLbyPA2x65Kgf80OJFdroafW" crossorigin="anonymous"'
			]
		],
		'css'=>[
			'materialicons'=>['src'=>'https://fonts.googleapis.com/icon?family=Material+Icons'],
			'slick'=>[
				'set'=>[
					['src'=>'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css'],
					['src'=>'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css'],
				]
			],
			'bootstrap'=>[
				'src'=>'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css',
				'attr'=>'integrity="sha384-giJF6kkoqNQ00vy+HMDP7azOuL0xtbfIcaT9wjKHr8RbDVddVHyTfAAsrekwKmP1" crossorigin="anonymous"'
			]
		]
	];
	public $enqueued=[],$missed=[],$rendered=[];
	public function __construct($type){
		$this->type=$type;
	}
	public function enqueue($handler,$src=null,$deps=[]){
		if(isset($this->enqueued[$handler]) || isset($this->missed[$handler])){return;}
		if(isset(self::$regsitered[$this->type][$handler])){
			$deps=self::$regsitered[$this->type][$handler]['deps']??null;
			if(!empty($deps)){
				foreach((array)$deps as $dep){$this->enqueue($dep);}
			}
			$this->enqueued[$handler]=self::$regsitered[$this->type][$handler];
			return;
		}
		if(!isset($src)){$src=$handler;}
		if(strpos($src,'://')===false){
			global $site,$page;
			if(!$page->file_should_exists($src)){
				$this->missed[$handler]=1;
				return false;
			}
			if($src[0]==='/'){
				if(!is_null($site->use_relative_path)){
					$src=$page->path_to_root.substr($src,1);
				}
			}
		}
		if(!empty($deps)){
			foreach((array)$deps as $dep){$this->enqueue($dep);}
		}
		$this->enqueued[$handler]=compact('src','deps');
	}
	public function register($handler,$src,$deps=[]){
		self::$regsitered[$this->type][$handler]=compact('src','deps');
	}
	public function render($handlers=null){
		if(isset($handlers)){
			foreach((array)$handlers as $handler){$this->enqueue($handler);}
		}
		$handlers=array_keys($this->enqueued);
		$handlers=array_filter($handlers,function($handler){return empty($this->rendered[$handler]);});
		usort($handlers,function($a,$b){
			if(empty($this->enqueued[$a]['deps']) && empty($this->enqueued[$b]['deps'])){return 0;}
			if(in_array($a,$this->enqueued[$b]['deps']??[],1)){return -1;}
			if(in_array($b,$this->enqueued[$a]['deps']??[],1)){return 1;}
			return 0;
		});
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
				if(!empty($source['src'])){printf('<link rel="stylesheet" type="text/css" href="%s" %s/>',$source['src'],$source['attr']??'');}
				if(!empty($source['set'])){
					foreach($source['set'] as $item){
						printf('<link rel="stylesheet" type="text/css" href="%s" %s/>',$item['src'],$item['attr']??'');
					}
				}
				break;
		}
		$this->rendered[$handler]=true;
		return true;
	}
}