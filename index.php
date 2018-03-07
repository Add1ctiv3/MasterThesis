<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/includes/boot.php")) { die("Boot file was not found!"); }

include_once(dirname(__FILE__) . "/includes/boot.php");

?>