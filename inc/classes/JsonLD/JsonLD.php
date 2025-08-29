<?php
namespace Catpow\JsonLD;

abstract class JsonLD{
	public static function render($args){
		$data=array_merge([
			"@context"=>"https://schema.org",
		],static::get_data($args));
		printf('<script type="application/ld+json">%s</script>',json_encode($data,0755));
	}
	public static function get_data($args){
		return [];
	}
}