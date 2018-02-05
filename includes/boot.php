<?php

set_time_limit(-1);
ignore_user_abort(true);
ini_set('max_execution_time', -1);

error_reporting(E_ALL ^ E_NOTICE);
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);

date_default_timezone_set('Europe/Athens');

session_start();

header('contentType: text/html; charset=UTF-8');

if (!defined('__ROOTFILE__')) { die("Direct access is not allowed!"); }

if (!defined('__BOOTFILE__')) { define('__BOOTFILE__', true); }

if (!defined('ROOT_PATH')) { define('ROOT_PATH', dirname(__FILE__) . "/../"); }

if (!defined('CLASSES_PATH')) { define('CLASSES_PATH', ROOT_PATH . "/classes/"); }

if (!defined('TEMPLATES_PATH')) { define('TEMPLATES_PATH', ROOT_PATH . "/templates/"); }

if (!defined('PAGES_PATH')) { define('PAGES_PATH', ROOT_PATH . "/pages/"); }

if (!defined('INCLUDES_PATH')) { define('INCLUDES_PATH', ROOT_PATH . "/includes"); }

if (!file_exists(INCLUDES_PATH . "/config.php")

 || !file_exists(INCLUDES_PATH . "/functions.php")

 || !file_exists(CLASSES_PATH . "/class.config.php")

 || !file_exists(CLASSES_PATH . "/class.db.php")

 || !file_exists(CLASSES_PATH . "/class.settings.php")

 || !file_exists(CLASSES_PATH . "/class.url.php")

 || !file_exists(CLASSES_PATH . "/class.template.php")

 || !file_exists(CLASSES_PATH . "/class.user.php") 
 
 || !file_exists(CLASSES_PATH . "/entity_classes/gender.php") 
 
 || !file_exists(CLASSES_PATH . "/entity_classes/person.php") 
 
 || !file_exists(CLASSES_PATH . "/entity_classes/telephone.php")

 || !file_exists(CLASSES_PATH . "/entity_classes/telecommunication.php")

 || !file_exists(CLASSES_PATH . "/class.log.php")

 || !file_exists(CLASSES_PATH . "/DatabaseInserter.php")

 || !file_exists(CLASSES_PATH . "/entity_classes/Dataset.php")

|| !file_exists(CLASSES_PATH . "/Visualizer.php")

 ) { die("Core files are missing"); }

 

include_once(CLASSES_PATH . "/class.config.php");

include_once(INCLUDES_PATH . "/config.php");

include_once(INCLUDES_PATH . "/functions.php");

include_once(CLASSES_PATH . "/class.db.php");

include_once(CLASSES_PATH . "/class.settings.php");

include_once(CLASSES_PATH . "/class.user.php");

include_once(CLASSES_PATH . "/class.url.php");

include_once(CLASSES_PATH . "/class.template.php");

include_once(CLASSES_PATH . "/Visualizer.php");

include_once(CLASSES_PATH . "/entity_classes/gender.php");

include_once(CLASSES_PATH . "/entity_classes/person.php");

include_once(CLASSES_PATH . "/entity_classes/telephone.php");

include_once(CLASSES_PATH . "/entity_classes/telecommunication.php");

include_once(CLASSES_PATH . "/class.log.php");

include_once(CLASSES_PATH . "/DatabaseInserter.php");

include_once(CLASSES_PATH . "/entity_classes/Dataset.php");

include_once(ROOT_PATH . '/vendor/autoload.php');

include_once(ROOT_PATH . '/vendor/fileUpload/class.fileuploader.php');

include_once(ROOT_PATH . 'vendor/dijkstra/Dijkstra.php');

Settings::init();

User::authenticate();

if (!defined("__AJAXFILE__")) { Url::parse(); }

?>