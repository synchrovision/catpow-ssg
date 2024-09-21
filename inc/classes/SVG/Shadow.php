<?php
namespace Catpow\SVG;
use Catpow\Scss;
class Shadow extends Filter{
	protected function init(){
		$this->filters=[
			['shadow','d'=>$this->props['d']??4]
		];
	}
}