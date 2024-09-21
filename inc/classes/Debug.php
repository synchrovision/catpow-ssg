<?php
namespace Catpow;
class Debug{
	public static function dump($val){
		echo '<div id="cp_log">';
		$bt=debug_backtrace()[0];
		printf('<small>%s(%s)</small><br/>',basename($bt['file']),$bt['line']);
		$fnc_dump_as_table=function($vals,$refs=[])use(&$fnc_dump_as_table){
			if(is_array($vals) || is_object($vals)){
				if(is_object($vals)){
					if(in_array($vals,$refs)){echo '...';return;}
					else{$refs[]=$vals;}
				}
				echo '<table>';
				foreach($vals as $key=>$val){
					printf('<tr><th>%s<span class="type">(%s)</span></th><td>',$key,is_object($val)?get_class($val):gettype($val));
					$fnc_dump_as_table($val,$refs);
					echo('</td></tr>');
				}
				echo '</table>';
			}
			else{
				var_export($vals);
			}
		};
		$fnc_dump_as_table($val);
		echo('</div>');
		self::dump_style();
	}
	public static function dump_style(){
		?>
<style>
#cp_log {
	display: block;
	position: fixed;
	bottom: 10px;
	right: 10px;
	padding: 10px;
	z-index: 100000;
	min-height: auto;
	max-height: 90%;
	height: auto;
	min-width: auto;
	max-width: 90%;
	width: auto;
	overflow-y: auto;
	text-align: left;
	background-color: #fefefe;
	opacity: 0.9;
	transform: -webkit- translateZ(100px);
	transform: translateZ(100px);
}
#cp_log small {
	color: #444;
}
#cp_log table {
	border-collapse: collapse;
	border-spacing: 0px;
}
#cp_log table tr th, #cp_log table tr td {
	padding: 5px;
	border-style: solid;
	border-width: 1px;
	border-color: rgba(68, 68, 68, 0.5);
	text-align: left;
	line-height: 100%;
	color: rgba(68, 68, 68, 0.8);
}
#cp_log table tr th {
	font-size: 10px;
	font-weight: bold;
	vertical-align: top;
	background-color: rgba(0, 124, 186, 0.1);
	cursor: pointer;
}
#cp_log table tr th .type {
	display: block;
	font-size: 8px;
	font-weight: normal;
}
#cp_log table tr td {
	font-size: 8px;
	vertical-align: middle;
	-webkit-transition: 0.5s;
	transition: 0.5s;
}
#cp_log table tr.close td::before {
	content: '…';
}
#cp_log table tr.close td table {
	display: none;
}
</style>
<?php
	}
}


?>