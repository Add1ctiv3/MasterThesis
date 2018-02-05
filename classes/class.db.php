<?php

if (!defined('__BOOTFILE__')) { die("Direct access is not allowed!"); }

class DB 
{
    public $dbh;
    static $instance;
    
    private function __construct() {
        try {
            $this->dbh = new PDO("mysql:dbhost=" . Config::read("mysql.host") . ";dbname=" . Config::read("mysql.name"), Config::read("mysql.user"), Config::read("mysql.pass"), array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
        }
        catch(PDOException $e) {
            die("System's database connection failed: " . $e->getMessage());
        }
        
		$this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        
        $this->dbh->query("SET NAMES utf8");
    }   
    
    public static function get() {
        if (!isset(self::$instance)) {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        
        return self::$instance;
    }
}
?>