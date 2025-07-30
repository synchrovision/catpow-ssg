<?php
ini_set("error_log","php://stdout");

if(!defined('BASE_HOST')){define('BASE_HOST','localhost:8000');}
if(!defined('SSE_HOST')){define('SSE_HOST','localhost:8001');}

define('BASE_URL','http://'.BASE_HOST);
define('SSE_URL','http://'.SSE_HOST);

define('APP_DIR',dirname(__DIR__));
define('APP_NAME',basename(APP_DIR));
if(!defined('ABSPATH')){define('ABSPATH',dirname(APP_DIR));}
define('APP_URL',BASE_URL.'/'.APP_NAME);

define('CP_DIR',APP_DIR.'/controlpanel');
define('CP_URL',APP_URL.'/controlpanel');

define('API_URL',APP_URL.'/api');
define('INC_DIR',APP_DIR.'/inc');
define('CONF_DIR',dirname(APP_DIR).'/_config');
define('TMPL_DIR',dirname(APP_DIR).'/_tmpl');