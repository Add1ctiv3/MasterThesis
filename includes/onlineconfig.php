<?php

if (!defined('__BOOTFILE__')) { die("Direct access is not allowed!"); }

/**
* MSSQL Database Info
*/
Config::write("mssql.host", "");
Config::write("mssql.name", "");

/**
* Website's MySQL Info 
*/
Config::write("mysql.host", "localhost");
Config::write("mysql.user", "addom_mai");
Config::write("mysql.pass", "F1^2xzEXgQ");
Config::write("mysql.name", "addom_masterdb");
Config::write("mysql.prefix", "ix_");

/**
* System's configurations 
*/
Config::write("default.controller", "user");
Config::write("default.action", "home");
Config::write("session.prefix", "fhSA232_FaS_");

?>