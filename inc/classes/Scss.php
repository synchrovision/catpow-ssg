<?php
namespace Catpow;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Type;
use ScssPhp\ScssPhp\ValueConverter;
use ScssPhp\ScssPhp\Node\Number;
use ScssPhp\ScssPhp\Exception\CompilerException;

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
		$scss_file_uri=str_replace(\ABSPATH,'',$scss_file);
		if(file_exists($f=\TMPL_DIR.$scss_file_uri)){return $f;}
		if(file_exists($f=\TMPL_DIR.str_replace('/css/','/_scss/',$scss_file_uri))){return $f;}
		if(file_exists($f=\TMPL_DIR.str_replace('/css/','/scss/',$scss_file_uri))){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,$scss_file_uri)){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/css/','/scss/',$scss_file_uri))){return $f;}
		if($f=Tmpl::get_tmpl_file_for_file_in_dir(\TMPL_DIR,str_replace('/css/','/_scss/',$scss_file_uri))){return $f;}
		return false;
	}
	public static function get_scssc(){
		if(isset(static::$scssc)){return static::$scssc;}
		$scssc = new Compiler();
		$scssc->addImportPath(\ABSPATH.'/');
		$scssc->addImportPath(\CONF_DIR.'/');
		$scssc->addImportPath(\ABSPATH.'/_scss/');
		$scssc->addImportPath(\INC_DIR.'/scss/');
		$scssc->setIgnoreErrors(true);
		$scssc->registerFunction('debug',function($args){
			error_log(var_export($args,1));
			return false;
		});
		$scssc->registerFunction('get-real-type',function($args){
			error_log(var_export($args[0][0],1));
			return [TYPE::T_KEYWORD,$args[0][0]];
		});
		$scssc->registerFunction('list-elements',function($args)use($scssc){
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
		$scssc->registerFunction('list-csv',function($args)use($scssc){
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
		$scssc->registerFunction('translate-color',function($args){
			$args=array_map([static::$scssc,'compileValue'],$args);
			$color=self::translate_color($args[0],$args[1]==='false'?100:(int)$args[1],$args[2]==='false'?1:(float)$args[2]);
			if(empty($color)){return Compiler::$false;}
			return [TYPE::T_KEYWORD,$color];
		});
		$scssc->registerFunction('get-color-vars',function($args){
			$vars=[];
			foreach(static::parse_map_data($args[0]) as $key=>$val){
				if(preg_match('/\d/',$key)){continue;}
				if(in_array($key,['hr','hs'])){$$key=$val;continue;}
				if($val==='transparent'){continue;}
				$lch=Colors::to_oklch($val);
				foreach($lch as $p=>$v){
					if($p==='h'){
						$vars["--cp-root-tones-{$key}-h"]=$v;
						$vars["--cp-container-tones-{$key}-h"]=$v;
					}
					$vars["--cp-tones-{$key}-{$p}"]=$v;
				}
				$vars["--cp-tones-{$key}-t"]=(1-$lch['l'])/100;
			}
			$vars["--cp-tones-hr"]=$hr??20;
			$vars["--cp-tones-hs"]=$hs??0;
			return self::create_map_data($vars);
		});
		$scssc->registerFunction('get-color-classes',function($args){
			$classes=[];
			if(!empty($args[0])){
				foreach(static::parse_map_data($args[0]) as $key=>$val){
					if(preg_match('/\d/',$key) || in_array($key,['hr','hs']) || $val==='transparent'){continue;}
					foreach(range(-6,6) as $n){
						$classes['.is-color'.$n]["--cp-tones-{$key}-h"]="calc(var(--cp-root-tones-{$key}-h) + var(--cp-tones-hr,20) * {$n} + var(--cp-tones-hs,0))";
						$classes['.is-color'.$n]["--cp-container-tones-{$key}-h"]="var(--cp-tones-{$key}-h)";
						$classes['.is-color_'.$n]["--cp-tones-{$key}-h"]="calc(var(--cp-container-tones-{$key}-h) + var(--cp-tones-hr,20) * {$n} + var(--cp-tones-hs,0))";
					}
				}
			}
			return self::create_map_data($classes);
		});
		return static::$scssc=$scssc;
	}
	public static function translate_color($color,$tint=100,$alpha=1){
		if(preg_match('/^([a-z]{1,3})?(_|\-\-)?(\-?\d+)?$/',$color,$matches)){
			$key=$matches[1]?:'m';
			$sep=$matches[2]??null;
			$staticHue=$sep==='--';
			$relativeHue=$sep==='_';
			$num=$matches[3]??null;
			$f='var(--cp-tones-'.$key.'-%s)';
			$cf='var(--cp-container-tones-'.$key.'-%s)';
			$rf='var(--cp-root-tones-'.$key.'-%s)';
			$color=sprintf(
				'oklch(%s %s %s / %s)',
				$args[1]==='false'?sprintf($f,'l'):sprintf('calc(1 - '.$f.' * %s)','t',$tint),
				sprintf($f,'c'),
				is_null($num)?
				sprintf($f,'h'):
				($staticHue?
				 	$num:
				 	(($num==='0' || $num==='6')?
					 	sprintf($relativeHue?$cf:$rf,'h'):
					 	sprintf('calc('.($relativeHue?$cf:$rf).' + var(--cp-tones-hr) * %s + var(--cp-tones-hs))','h',(int)$num-6)
					)
				),
				$args[2]==='false'?'var(--cp-tones-'.$key.'-a,1)':'calc(var(--cp-tones-'.$key.'-a,1) * '.$alpha.')'
			);
		}
		elseif(preg_match('/^([a-z]{1,3})\-([a-z]+)$/',$color,$matches)){
			$key1=$matches[1];
			$key2=$matches[2];
			$t=$args[1]?:50;
			$t/=100;
			$f='calc(var(--cp-tones-%2$s-%1$s) * '.$t.' + var(--cp-tones-%3$s-%1$s) * '.(1-$t).')';
			$a=sprintf($f,'a',$key1,$key2);
			if($args[2]!=='false'){
				$a=sprintf('calc(%s * %s)',$a,$args[2]);
			}
			$color=sprintf(
				'oklch(%s %s %s / %s)',
				sprintf($f,'l',$key1,$key2),
				sprintf($f,'c',$key1,$key2),
				sprintf($f,'h',$key1,$key2),
				$a
			);
		}
		return $color;
	}
	public static function compile($scss_file,$css_file,$source_map=true){
		if(version_compare(PHP_VERSION, '5.4')<0)return;
		$scssc=self::get_scssc();
		$modified_time=filemtime($scss_file);
		if(
			file_exists($config_file=ABSPATH.'/_scss/style_config.scss') ||
			file_exists($config_file=CONF_DIR.'/style_config.scss')
		){
			$modified_time=max($modified_time,filemtime($config_file));
		}
		if(!is_dir(dirname($css_file))){mkdir(dirname($css_file),0777,true);}
		preg_match_all('/@import\s+(\'|")(.+?)\1/',file_get_contents($scss_file),$all_matches,\PREG_SET_ORDER);
		$dir=dirname($scss_file).'/';
		foreach($all_matches as $matches){
			if(strpos($matches[2],'://')!==false){continue;}
			if(
				file_exists($relative_file=$dir.$matches[2]) ||
				file_exists($relative_file=$relative_file.'.scss')
			){
				$modified_time=max($modified_time,filemtime($relative_file));
			}
		}
		if(
			!file_exists($css_file) or
			filemtime($css_file) < $modified_time
		){
			try{
				self::$current_scss_file=$scss_file;
				if($source_map){
					$scssc->setSourceMap(Compiler::SOURCE_MAP_FILE);
					$scssc->setSourceMapOptions([
						'sourceMapWriteTo'=>$css_file.'.map',
						'sourceMapURL'=>'./'.basename($css_file).'.map',
						'sourceMapFilename'=>basename($css_file).'.map',
						'sourceMapBasepath'=>$_SERVER['DOCUMENT_ROOT'],
						'sourceRoot'=>'/'
					]);
				}
				else{
					$scssc->setSourceMap(Compiler::SOURCE_MAP_NONE);
				}
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