<?php

if (!defined('__BOOTFILE__')) { die("Direct access is not allowed!"); }

class Config
{
    static $Configurations = array();
    
    public static function write($NAME, $VALUE) {
        self::$Configurations[$NAME] = $VALUE;
    }    
    
    public static function read($NAME) {
        return self::$Configurations[$NAME];
    }
}
?>