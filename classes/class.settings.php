<?php

if (!defined('__BOOTFILE__')) { die("Direct access is not allowed!"); }

class Settings 
{
    static $SETTINGS = array();
    
    public static function init() {
        try {
            $stmt = DB::get()->dbh->query("SELECT * FROM " . Config::read("mysql.prefix") . "settings");
        }
        catch(PDOException $e) {
            die("Unable to load settings: " . $e->getMessage());
        }
        
        while ($settingsData = $stmt->fetch()) {
            self::$SETTINGS[$settingsData->setting_name] = $settingsData->setting_value;
        }
    }
    
    public static function read($SETTING_NAME) {
        return self::$SETTINGS[$SETTING_NAME];
    }
    
    public static function create($SETTING_NAME, $SETTING_VALUE) {
        try {
            $stmt = DB::get()->dbh->prepare("INSERT INTO " . Config::read("mysql.prefix") . "settings (setting_name, setting_value) VALUES (:name, :value)");
            $stmt->bindParam(":name", $SETTING_NAME, PDO::PARAM_STR);
            $stmt->bindParam(":value", $SETTING_VALUE, PDO::PARAM_STR);
            $stmt->execute();
        }
        catch(PDOException $e) {
            if ((int)$e->getCode() === 23000) {
                try {
                    $stmt = DB::get()->dbh->prepare("UPDATE " . Config::read("mysql.prefix") . "settings SET setting_value = :value WHERE setting_name = :name");
                    $stmt->bindParam(":name", $SETTING_NAME, PDO::PARAM_STR);
                    $stmt->bindParam(":value", $SETTING_VALUE, PDO::PARAM_STR);
                    $stmt->execute();
                }   
                catch(PDOException $e) {
                    die("Unable to update setting <b>" . $SETTING_NAME . "</b>: " . $e->getMessage());
                }
                
                return true;
            }            
            
            die("Unable to create setting <b>" . $SETTING_NAME . "</b>: " . $e->getMessage());
        }
    }
}
?>