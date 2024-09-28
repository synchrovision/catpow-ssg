<?php
namespace Catpow\RXF;
abstract class price extends RXF{
	static $formats=[
		"/([\d,]+)(円)/"=>'<span class="{$class}"><span class="{$class}__num">$1</span><span class="{$class}__unit">$2</span></span>',
		"/(¥|\$)([\d,\.]+)/"=>'<span class="{$class}"><span class="{$class}__unit">$1</span><span class="{$class}__num">$2</span></span>'
	];
}