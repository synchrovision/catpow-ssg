<?php
namespace Catpow;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Type;
use ScssPhp\ScssPhp\ValueConverter;
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\Exception\CompilerException;
use Spatie\Color\Hsl;

class Scss{
	public static $scssc,$current_scss_file;
	public static function compile_for_file($file){
		if($scss_file=self::get_scss_file_for_file($file)){
			self::compile($scss_file,$file);
		}
	}
	public static function get_scss_file_for_file($file){
		$scss_file=substr($file,0,-3).'scss';
		if(file_exists($scss_file)){return $scss_file;}
		if(file_exists($f=str_replace('/css/','/_scss/',$scss_file))){return $f;}
		if(file_exists($f=str_replace('/css/','/scss/',$scss_file))){return $f;}
		if(file_exists($f=str_replace(ABSPATH,TMPL_DIR,$scss_file))){return $f;}
		if(file_exists($f=str_replace([ABSPATH,'/css/'],[TMPL_DIR,'/_scss/'],$scss_file))){return $f;}
		if(file_exists($f=str_replace([ABSPATH,'/css/'],[TMPL_DIR,'/scss/'],$scss_file))){return $f;}
		return false;
	}
	public static function get_scssc(){
		if(isset(static::$scssc)){return static::$scssc;}
		$scssc = new Compiler();
		$scssc->addImportPath(ABSPATH.'/');
		$scssc->addImportPath(CONF_DIR.'/');
		$scssc->addImportPath(ABSPATH.'/_scss/');
		$scssc->addImportPath(INC_DIR.'/scss/');
		$scssc->setSourceMap(Compiler::SOURCE_MAP_FILE);
		$scssc->setIgnoreErrors(true);
		$scssc->registerFunction('list_elements',function($args)use($scssc){
			if(empty($args[0][2][0]) || empty($args[1][2][0])){return Compiler::$emptyList;}
			try{
				$file=self::get_source_file($args[0][2][0]);
				$xml=simplexml_load_string(preg_replace('/ xmlns=".*?"/','',file_get_contents($file)));
				$elements=[];
				if(!empty($elements=$xml->xpath($args[1][2][0]))){
					foreach($elements as $i=>$element){
						$keys=[];
						$vals=[];
						foreach($element->attributes() as $key=>$val){
							$val=(string)$val;
							$keys[]=[TYPE::T_KEYWORD,$key];
							$vals[]=is_numeric($val)?new Number($val,''):[TYPE::T_KEYWORD,$val];
						}
						$elements[$i]=[TYPE::T_MAP,$keys,$vals];
					}
				}
				return [TYPE::T_LIST,',',$elements];
			}
			catch(CompilerException $e){
				return Compiler::$emptyList;
			}
		});
		$scssc->registerFunction('list_csv',function($args)use($scssc){
			if(empty($args[0][2][0])){return Compiler::$emptyList;}
			try{
				$file=self::get_source_file($args[0][2][0]);
				$csv=new CSV($file);
				$rows=[];
				if(!empty($rows=$csv->select())){
					foreach($rows as $i=>$row){
						$keys=[];
						$vals=[];
						foreach($row as $key=>$val){
							$val=(string)$val;
							$keys[]=[TYPE::T_KEYWORD,$key];
							$vals[]=is_numeric($val)?new Number($val,''):[TYPE::T_KEYWORD,$val];
						}
						$rows[$i]=[TYPE::T_MAP,$keys,$vals];
					}
				}
				return [TYPE::T_LIST,',',$rows];
			}
			catch(CompilerException $e){
				return Compiler::$emptyList;
			}
		});
		$scssc->registerFunction('translate_color',function($args){
			$args=array_map([static::$scssc,'compileValue'],$args);
			$color=self::translate_color($args[0],$args[1]==='false'?100:(int)$args[1],$args[2]==='false'?1:(float)$args[2]);
			if(empty($color)){return Compiler::$false;}
			return [TYPE::T_KEYWORD,$color];
		});
		$scssc->registerFunction('get_color_vars',function($args){
			$vars=[];
			foreach(static::parse_map_data($args[0]) as $key=>$val){
				if(preg_match('/\d/',$key)){continue;}
				if(in_array($key,['hr','hs'])){$$key=$val;}
				if($val==='transparent'){continue;}
				$color=\Spatie\Color\Factory::fromString($val);
				$hsla=$color->toHsla(method_exists($color,'alpha')?hexdec($color->alpha())/255:1);
				foreach(['h'=>'hue','s'=>'saturation','l'=>'lightness','a'=>'alpha'] as $p=>$prop){
					$$p=round($hsla->{$prop}(),2);
					if($p==='h'){
						$vars["--root-tones-{$key}-h"]=$h;
						$vars["--container-tones-{$key}-h"]=$h;
					}
					$vars["--tones-{$key}-{$p}"]=($p==='h' || $p==='a')?$$p:$$p.'%';
				}
				$vars["--tones-{$key}-t"]=(1-$l/100).'%';
			}
			$vars["--tones-hr"]=$hr??20;
			$vars["--tones-hs"]=$hs??0;
			return self::create_map_data($vars);
		});
		$scssc->registerFunction('get_color_classes',function($args){
			$classes=[];
			foreach(static::parse_map_data($args[0]) as $key=>$val){
				if(preg_match('/\d/',$key) || in_array($key,['hr','hs']) || $val==='transparent'){continue;}
				foreach(range(-6,6) as $n){
					$classes['.is-color'.$n]["--tones-{$key}-h"]="calc(var(--root-tones-{$key}-h) + var(--tones-hr,20) * {$n} + var(--tones-hs,0))";
					$classes['.is-color'.$n]["--container-tones-{$key}-h"]="var(--tones-{$key}-h)";
					$classes['.is-color_'.$n]["--tones-{$key}-h"]="calc(var(--container-tones-{$key}-h) + var(--tones-hr,20) * {$n} + var(--tones-hs,0))";
				}
			}
			return self::create_map_data($classes);
		});
		return static::$scssc=$scssc;
	}
	public static function translate_color($color,$tint=100,$alpha=1){
		if($color==='none' || $color==='inherit' || !empty(Colors::NAMED_COLORS[$color])){return false;}
		if(preg_match('/^([a-z]+)?(_|\-\-)?(\-?\d+)?$/',$color,$matches)){
			$key=$matches[1]?:'m';
			$sep=$matches[2]??null;
			$staticHue=$sep==='--';
			$relativeHue=$sep==='_';
			$num=$matches[3]??null;
			$f='var(--tones-'.$key.'-%s)';
			$rf='var(--root-tones-'.$key.'-%s)';
			return sprintf(
				'hsla(%s,%s,%s,%s)',
				is_null($num)?
				sprintf($f,'h'):
				($staticHue?
					$num:
					(($num==='0')?
						sprintf($relativeHue?$f:$rf,'h'):
						sprintf('calc('.($relativeHue?$f:$rf).' + var(--tones-hr,20) * %s + var(--tones-hs,0))','h',$num)
					)
				),
				sprintf($f,'s'),
				(empty($tint) || $tint==100)?sprintf($f,'l'):sprintf('calc(100%% - '.$f.' * %s)','t',$tint),
				(empty($alpha) || $alpha>=1)?'var(--tones-'.$key.'-a)':'calc(var(--tones-'.$key.'-a) * '.$alpha.')'
			);
		}
		return false;
	}
	public static function compile($scss_file,$css_file){
		if(version_compare(PHP_VERSION, '5.4')<0)return;
		$scssc=self::get_scssc();
		if(
			file_exists($config_file=ABSPATH.'/_scss/style_config.scss') ||
			file_exists($config_file=CONF_DIR.'/style_config.scss')
		){
			$style_config_modified_time=filemtime($config_file);
		}
		else{
			$style_config_modified_time=0;
		}
		if(!is_dir(dirname($css_file))){mkdir(dirname($css_file),0777,true);}
		if(
			!file_exists($css_file) or
			filemtime($css_file) < max(
				filemtime($scss_file),
				$style_config_modified_time
			)
		){
			try{
				self::$current_scss_file=$scss_file;
				$scssc->setSourceMapOptions([
					'sourceMapWriteTo'=>$css_file.'.map',
					'sourceMapURL'=>'./'.basename($css_file).'.map',
					'sourceMapFilename'=>basename($css_file).'.map',
					'sourceMapBasepath'=>$_SERVER['DOCUMENT_ROOT'],
					'sourceRoot'=>'/'
				]);
				$css=$scssc->compile(file_get_contents($scss_file),$scss_file);
				self::$current_scss_file=null;
			}catch(Exception $e){
				echo $e->getMessage();
			}
			file_put_contents($css_file,$css);
			usleep(1000);
		}
	}
	public static function parse_map_data($map){
		if($map[0]!==TYPE::T_MAP){return [];}
		return array_combine(
			array_map([static::$scssc,'compileValue'],$map[1]),
			array_map([static::$scssc,'compileValue'],$map[2])
		);
	}
	public static function create_map_data($data){
		return [
			TYPE::T_MAP,
			array_map(function($key){return [TYPE::T_KEYWORD,$key];},array_keys($data)),
			array_map(function($val){
				if(is_array($val)){return self::create_map_data($val);}
				return [TYPE::T_KEYWORD,$val];
			},array_values($data))
		];
	}
	public static function get_source_file($file){
		$paths=array_merge([dirname(self::$current_scss_file).'/'],self::get_scssc()->getCompileOptions()['importPaths']);
		foreach($paths as $path){
			if(file_exists($f=$path.$file)){return $f;}
		}
		return null;
	}
}