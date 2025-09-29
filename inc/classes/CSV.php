<?php
namespace Catpow;

class CSV implements \Iterator,\ArrayAccess{
	const CAST_NUMERIC=1,CAST_BOOL=2,FILL_COLUMN=4;
	public $data=array(),$file,$current_index=1;
	private $hash,$tree,$depth;
	
	public function rewind():void{
		$this->current_index=1;
	}
	#[\ReturnTypeWillChange]
	public function current(){
		return array_combine($this->data[0],$this->data[$this->current_index]);
	}
	#[\ReturnTypeWillChange]
	public function key(){
		return $this->current_index;
	}
	public function next():void{
		$this->current_index++;
	}
	public function valid():bool{
		return isset($this->data[$this->current_index]);
	}
	
	public function offsetSet($offset,$value):void{
		$this->data[$offset+1]=array_values($value);
	}
	public function offsetExists($offset):bool{
		isset($this->data[$offset+1]);
	}
	public function offsetUnset($offset):void{
		unset($this->data[$offset+1]);
	}
	#[\ReturnTypeWillChange]
	public function offsetGet($offset){
		if(empty($this->data[$offset+1])){return null;}
		return array_combine($this->data[0],$this->data[$offset+1]);
	}
	
	public function __construct($csv,$flags=0){
		if(empty($csv)){return;}
		$cast_numeric=$flags&self::CAST_NUMERIC;
		$cast_bool=$flags&self::CAST_BOOL;
		$fill_column=$flags&self::FILL_COLUMN;
		if(is_array($csv)){
			$this->data=array_map(function($row){
				return array_values($row);
			},$csv);
		}
		else{
			$this->file=$csv;
			$csv=fopen($csv,'r');
			if(empty($fill_column)){
				while($row=fgetcsv($csv,escape:"\\")){
					array_push($this->data,self::cast_values($row,$flags));
				}
			}
			else{
				$keys=fgetcsv($csv,escape:"\\");
				array_push($this->data,$keys);
				if($fill_column===true){$fill_column=count($keys);}
				if(is_numeric($fill_column)){$fill_column=range(0,$fill_column);}
				elseif(is_string($fill_column)){$fill_column=[array_search($fill_column,$keys)];}
				elseif(is_array($fill_column)){
					foreach($fill_column as $i=>$key){
						if(!is_numeric($key)){$fill_column[$i]=array_search($key,$keys);}
					}
				}
				$current_values=[];
				while($row=fgetcsv($csv,escape:"\\")){
					foreach($fill_column as $index){
						if(empty($row[$index])){$row[$index]=$current_values[$index]??null;}
						else{$current_values[$index]=$row[$index];}
					}
					array_push($this->data,self::cast_values($row,$flags));
				}
			}
		}
	}
	private static function cast_values($values,$flags){
		if($flags&(self::CAST_NUMERIC|self::CAST_BOOL)){
			foreach($values as $key=>$val){
				if($flags&self::CAST_NUMERIC && is_numeric($val)){$values[$key]=(float)$val;}
				if($flags&self::CAST_BOOL){
					if($val==='TRUE'){$values[$key]=true;}
					if($val==='FALSE'){$values[$key]=false;}
				}
			}
		}
		return $values;
	}
	public function is_flat(){
		foreach($this->data as $row){
			foreach($row as $val){
				if(is_array($val)){return false;}
			}
		}
		return true;
	}
	public function is_flatten(){
		foreach(reset($this->data) as $key){
			if(strpos($key,'/')!==false){return true;}
		}
		return false;
	}
	public function flatten(){
		if($this->is_flat()){return $this;}
		$flatten_datas=[];
		foreach($this->select() as $data){
			$flatten_datas[]=self::flatten_data($data);
		}
		$data_template=self::flatten_data(self::unflatten_data(call_user_func_array('array_merge',$flatten_datas)));
		foreach($data_template as $key=>$val){$data_template[$key]='';}
		$this->data=[array_keys($data_template)];
		foreach($flatten_datas as $data){
			$this->data[]=array_values(array_merge($data_template,$data));
		}
		return $this;
	}
	public static function flatten_data($data){
		$flatten_data=[];
		foreach($data as $key=>$val){
			if(is_array($val)){
				$val=self::flatten_data($val);
				foreach($val as $k=>$v){
					$flatten_data[$key.'/'.$k]=$v;
				}
			}
			else{
				$flatten_data[$key]=[$val];
			}
		}
		return $flatten_data;
	}
	public function unflatten(){
		if(!$this->is_flatten()){return $this;}
		$unflatten_datas=[];
		foreach($this->select() as $data){
			$unflatten_datas[]=self::unflatten_data($data);
		}
		$data_template=[];
		foreach($unflatten_datas[0] as $key=>$val){$data_template[$key]='';}
		$this->data=[array_keys($data_template)];
		foreach($unflatten_datas as $data){
			$this->data[]=array_values(array_merge($data_template,$data));
		}
		return $this;
	}
	public static function unflatten_data($data){
		$unflatten_data=[];
		foreach($data as $key=>$val){
			if(strpos($key,'/')!==false){
				eval('$unflatten_data[\''.str_replace(["'",'/'],["\'","']['"],$key).'\']=$val;');
			}
			else{
				$unflatten_data[$key]=$val;
			}
		}
		return $unflatten_data;
	}
	
	public static function loop($csv,$where=[]){
		$c=new self($csv);
		foreach($csv->select($where) as $i=>$row){yield $i=>$row;}
	}
	public static function get_csvs($dir=null,$fill_column=false){
		if(is_null($dir)){$dir=dirname(__DIR__,3).'/csv';}
		$csvs=[];
		foreach(glob($dir.'/*.csv') as $csv_file){
			$csvs[mb_substr(basename($csv_file),0,-4)]=new CSV($csv_file,$fill_column);
		}
		return $csvs;
	}
	
	public function __get($name){
		if($name=='keys')return $this->data[0];
		if($name=='items')return $this->select();
		if($name=='tree')return $this->collect();
	}
	
	public function save($file=null){
		if(isset($file)){$this->file=$file;}
		$f=fopen($this->file,'w');
		foreach($this->data as $i=>$row){
			fputcsv($f,$row);
		}
		fclose($f);
	}
	
	public function select($where=[],$orderby=false,$limit=0){
		$rtn=[];
		$keys=$this->data[0]??[];
		$limit=(int)$limit;
		for($r=1,$l=count($this->data);$r<$l;$r++){
			foreach($where as $key=>$cond){
				$c=array_search($key,$keys);
				if($c===false){continue 2;}
				if($this->test_value($this->data[$r][$c],$cond)===false){continue 2;}
			}
			$row=[];
			foreach($keys as $i=>$key){
				$row[$key]=&$this->data[$r][$i];
			}
			$rtn[]=$row;
			if(!$orderby && $limit-- === 1){return $rtn;}
		}
		if($orderby){
			if(is_a($orderby,'Closure')){usort($rtn,$orderby);}
			elseif(is_array($orderby)){
				$_orderby;
				foreach($orderby as $key=>$order){
					if(is_numeric($key)){
						$_orderby[$order]=1;
					}
					else{
						$_orderby[$key]=($order=='ASC')?1:-1;
					}
				}
				usort($rtn,function($a,$b)use($_orderby){
					foreach($_orderby as $key=>$order){
						if($a[$key] > $b[$key]){return $order;}
						if($a[$key] < $b[$key]){return $order*-1;}
					}
					return 0;
				});
			}
			else{
				if(is_numeric($orderby)){
					$orderby=$keys[$orderby];
				}
				usort($rtn,function($a,$b)use($orderby){
					if($a[$orderby] > $b[$orderby]){return 1;}
					if($a[$orderby] < $b[$orderby]){return -1;}
					return 0;
				});
			}
		}
		if($limit>0){return array_slice($rtn,0,$limit);}
		return $rtn;
	}
	public function collect($collect_by=0,$fill_key_column=true){
		$rtn=[];
		$keys=$this->data[0];
		if(is_numeric($collect_by)){$collect_by=range(0,$collect_by);}
		elseif(is_string($collect_by)){$collect_by=[array_search($collect_by,$this->data[0])];}
		elseif(is_array($collect_by)){
			foreach($collect_by as $i=>$key){
				if(!is_numeric($key)){$collect_by[$i]=array_search($key,$this->data[0]);}
			}
		}
		$current_keys=[];
		for($r=1,$l=count($this->data);$r<$l;$r++){
			$row=[];
			$sel='';
			foreach($collect_by as $i){
				if($fill_key_column){
					if(!empty($this->data[$r][$i])){$current_keys[$i]=$this->data[$r][$i];}
					$sel.="['{$current_keys[$i]}']";
				}
				else{$sel.="['{$this->data[$r][$i]}']";}
			}
			foreach($keys as $i=>$key){
				$row[$key]=&$this->data[$r][$i];
			}
			eval('$rtn'.$sel.'[]=$row;');
		}
		return $rtn;
	}
	public function column($col){
		$rtn=[];
		if(is_array($col) && count($col)===1){$col=$col[0];}
		if(!is_array($col)){
			if(!is_numeric($col)){$col=array_search($col,$this->data[0]);}
			return array_column(array_slice($this->data,1),$col);
		}
		$rtn=[];
		$current_keys=[];
		$collect_by=[];
		foreach($col as $i=>$c){
			$collect_by[$i]=!is_numeric($c)?array_search($c,$this->data[0]):$c;
		}
		$target_col=array_pop($collect_by);
		for($r=1,$l=count($this->data);$r<$l;$r++){
			$sel='';
			foreach($collect_by as $i=>$c){
				if(!empty($this->data[$r][$c])){$current_keys[$i]=$this->data[$r][$c];}
				$sel.="['{$current_keys[$i]}']";
			}
			eval('$rtn'.$sel.'=$this->data[$r][$target_col];');
		}
		return $rtn;
	}
	public function dict($keys=null,$where=[]){
		if(is_null($keys)){$keys=$this->data[0][0];}
		if(is_array($keys) && count($keys)===1){$keys=$keys[0];}
		if(!is_array($keys)){
			return array_column($this->select($where),null,$keys);
		}
		$rtn=[];
		$current_keys=[];
		$collect_by=[];
		foreach($keys as $key){
			$collect_by[]=!is_numeric($key)?array_search($key,$this->data[0]):$key;
		}
		for($r=1,$l=count($this->data);$r<$l;$r++){
			$sel='';
			foreach($collect_by as $i=>$c){
				if(!empty($this->data[$r][$c])){$current_keys[$i]=$this->data[$r][$c];}
				$sel.="['{$current_keys[$i]}']";
			}
			foreach($where as $key=>$cond){
				$c=array_search($key,$this->data[0]);
				if($c===false){continue 2;}
				if($this->test_value($this->data[$r][$c],$cond)===false){continue 2;}
			}
			$row=array_combine($this->data[0],$this->data[$r]);
			eval('$rtn'.$sel.'=$row;');
		}
		return $rtn;
	}
	public function update($data,$where){
		$keys=$this->data[0];
		for($r=1,$l=count($this->data);$r<$l;$r++){
			foreach($where as $key=>$cond){
				$c=array_search($key,$keys);
				if($c===false){continue 2;}
				if($this->test_value($this->data[$r][$c],$cond)===false){continue 2;}
			}
			foreach($data as $key=>$val){
				$c=array_search($key,$keys);
				if($c===false){continue;}
				if(is_a($val,'Closure')){
					$val=$val($this->data[$r][$c]);
				}
				if(is_numeric($val)){$val=strval($val);}
				if(is_array($val)){$val=json_encode($val);}
				$this->data[$r][$c]=$val;
			}
		}
	}
	public function delete($where){
		$keys=$this->data[0];
		for($r=1;$r<count($this->data);$r++){
			foreach($where as $key=>$cond){
				$c=array_search($key,$keys);
				if($c===false){continue 2;}
				if($this->test_value($this->data[$r][$c],$cond)===false){continue 2;}
			}
			unset($this->data[$r]);$r--;
		}
	}
	public function insert($data){
		$row=[];
		if(is_array($data)){
			foreach($this->data[0] as $i=>$key){$row[]=$data[$key]?:$data[$i]?:'';}
		}
		elseif(is_object($data)){
			foreach($this->data[0] as $i=>$key){$row[]=$data->$key?:'';}
		}
		$this->data[]=$row;
		if(isset($this->file)){
			$f=fopen($this->file,'a');
			fputcsv($f,$row);
			fclose($f);
		}
	}
	
	public function ref($where){
		$keys=$this->data[0];
		for($r=1,$l=count($this->data);$r<$l;$r++){
			foreach($where as $key=>$cond){
				$c=array_search($key,$keys);
				if($c===false){continue 2;}
				if($this->test_value($this->data[$r][$c],$cond)===false){continue 2;}
			}
			$rtn=[];
			foreach($keys as $i=>$key){
				$rtn[$key]=&$this->data[$r][$i];
			}
			return $rtn;
		}
		return false;
	}
	
	private function test_value($val,$cond){
		if(is_a($cond,'Closure')){
			if(!$cond($val)){return false;}
		}
		if(is_bool($cond)){return $val===$cond;}
		if(is_numeric($cond)){$cond=strval($cond);}
		if(is_string($cond)){
			if(substr($cond,0,1)==='/'){
				if(!preg_match($cond,$val)){return false;}
			}
			elseif($val!==$cond){return false;}
		}
		if(is_array($cond)){
			if(!in_array($val,$cond)){return false;}
		}
		return true;
	}
	

	public function output_table($header_column=1,$class="csv_table"){
		printf("<table class='%s'>\n\t<thead>\n\t\t<tr>\n",$class);
		foreach($this->data[0] as $key){
			echo("\t\t\t<th>{$key}</th>\n");
		}
		echo("\t\t</tr>\n\t</thead>\n\t<tbody>\n");
		for($r=1;$r<count($this->data);$r++){
			echo("\t\t<tr>");
			foreach($this->data[$r] as $c=>$val){
				echo("\t\t\t");
				printf(($c<$header_column)?"<th>%s</th>\n":"<td>%s</td>\n",$val);
			}
			echo("\t\t</tr>");
		}
		echo("\t</tbody>\n</table>\n");
	}
	public function get_output_table($row_head=1,$col_head=1,$class="csv_table"){
		ob_start();
		$this->output_table($row_head,$col_head,$class);
		return ob_get_clean();
	}
	public function output(){
		$f=fopen('php://output','w');
		foreach($this->data as $i=>$row){
			fputcsv($f,$row);
		}
		fclose($f);
	}
	public function get_output(){
		ob_start();
		$this->output();
		return ob_get_clean();
	}
	public function download($fname='data'){
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.$fname.'.csv"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		$this->output();
		die();
	}
}


?>