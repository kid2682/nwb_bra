<?php
if (!defined('THINK_PATH'))	exit();

$config = require("config.inc.php");
$ignorechenk=require 'configs/ignorecheck.config.php';

$array = array( 	
    'URL_MODEL' => 0,
    'LANG_SWITCH_ON' => true,
    'DEFAULT_LANG' => 'zh-cn', // 默认语言
    'LANG_AUTO_DETECT' => true, // 自动侦测语言     
 	'APP_AUTOLOAD_PATH'=>'@.TagLib',//	
	'TMPL_ACTION_ERROR'     => 'public:error',
    'TMPL_ACTION_SUCCESS'   => 'public:success',
    'SHOW_PAGE_TRACE'=>false,	  //是否显示TRACE信息	
	'HTML_CACHE_ON'=>false,
);
return array_merge($config,$ignorechenk,$array);
?>