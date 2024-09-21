<?php
namespace Catpow\SVG;
use Catpow\Scss;
class Blur extends Filter{
	protected function init(){
		$this->filters=[
			['blur','d'=>$this->props['d']??8]
		];
	}
}