<?php
namespace Catpow\API;
use Catpow\VSCodeSettings as VSCS;

class VSCode{
	public static function request($req){
		VSCS::initSettingsData();
		VSCS::initCustomHTMLData();
		VSCS::initEmmetSnippets();
		return ['message'=>'init VSCode Settings'];
	}
}