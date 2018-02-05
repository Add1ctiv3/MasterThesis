<?php

if (!defined("__BOOTFILE__")) { die("Direct access is not allowed!"); }

class User
{
	public static function isLoggedIn() {
        return __S('user-loggedIn');
    }
    
    public static function isAdmin($username) {
        
		if($username == null || $username == "") { return false; }
		
		$type = 'administrator';
		
		try {
			$stmt = DB::get()->dbh->prepare("SELECT count(*) AS number FROM " . Config::read('mysql.prefix') . "users WHERE username = :username AND type = :typ");
			$stmt->bindParam(":username", $username, PDO::PARAM_STR);
			$stmt->bindParam(":typ", $type, PDO::PARAM_STR);
			$stmt->execute();
		}
		catch(PDOException $e) {
			die("Unable to check if user is Admin: " . $e->getMessage());
		}
				
		$f = $stmt->fetch();

		if ($f->number == 1) { return true; }
        
        return false;
    }
	    
    public static function authenticate() {
        if (self::isLoggedIn()) {
			
			$usr = __S('user-username');
			
            try {
                $stmt = DB::get()->dbh->prepare("SELECT * FROM " . Config::read('mysql.prefix') . "users WHERE username = :username");
                $stmt->bindParam(":username", $usr, PDO::PARAM_STR);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die("Unable to authenticate user: " . $e->getMessage());
            }
            
            if (!$userData = $stmt->fetch()) { return self::logout(); }
			
			$uniq = date("d") . "." . date("m") . "." . date("Y") . "_" .  __S('user-username');
			S('sessionUniqueId', $uniq);
            
            return true;
        }
        
        self::logout();
    }
    
    public static function logout() {
		
        DS('user-loggedIn');
		DS('user-username');
		DS("sessionUniqueId");
		
        return true;
    }
	
	public static function getLoggedUser() {
		
		if (self::isLoggedIn()) {
			
			$usr = __S('user-username');
			
            try {
                $stmt = DB::get()->dbh->prepare("SELECT * FROM " . Config::read('mysql.prefix') . "users WHERE username = :username");
                $stmt->bindParam(":username", $usr, PDO::PARAM_STR);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die("Unable to authenticate user: " . $e->getMessage());
            }
            
            $userData = $stmt->fetch();
			
			if(!$userData) { self::logout(); }
		
			return array("username" => __S('user-username'),
						 "email" => $userData->email,
						 "lastname" => $userData->lastname,
						 "firstname" => $userData->firstname,
						 "type" => $userData->type,
						 "access-level" => $userData->access_level,
						 "last-login" => $userData->last_login,
						 "icon" => $userData->icon);
		}
		
		self::logout();
	}
	
	public static function getUsers() {
		
		if(!self::isLoggedIn()) { die("You must be logged in"); }
		if(!self::isAdmin(__S("user-username"))) { die("You must be an admin!"); }
		
		try {
			$usr = __S("user-username");
			$stmt = DB::get()->dbh->prepare("SELECT * FROM " . Config::read('mysql.prefix') . "users");
			$stmt->bindParam(":username", $usr, PDO::PARAM_STR);
			$stmt->execute();
		}
		catch(PDOException $e) {
			die("Unable to get users: " . $e->getMessage());
		}
		
		$users = array();
		
		while($f = $stmt->fetch()) {
			
			array_push($users, array(
									"username" => $f->username,
									"lastname" => $f->lastname,
									"firstname" => $f->firstname,
									"email" => $f->email,
									"blocked" => $f->blocked,
									"blocked_message" => $f->blocked_message,
									"last_login" => $f->last_login,
									"type" => $f->type,
									"access_level" => $f->access_level,
									"icon" => $f->icon
								));
			
		}
		
		return $users;
		
	}
	
	public static function updateUsersIcon() {
		
		if (self::isLoggedIn()) {
			
			$usr = __S('user-username');
			
            try {
				$path = $usr . ".png";
                $stmt = DB::get()->dbh->prepare("UPDATE " . Config::read('mysql.prefix') . "users SET icon=:icn WHERE username = :username");
                $stmt->bindParam(":username", $usr, PDO::PARAM_STR);
				$stmt->bindParam(":icn", $path, PDO::PARAM_STR);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die("Unable to update users icon: " . $e->getMessage());
            }                        
		}
	}
			
}
?>

