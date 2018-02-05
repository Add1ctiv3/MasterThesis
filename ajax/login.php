<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if($_POST['uri'] == "check_signup_username") {
				
	$result = array("result" => "success",
					"data" => array());
	
	if($_POST['username'] == "") {
		$result['result'] = "error";
		array_push($result['data'], array("field" => 'signup_username', "message" => 'Username can not be blank!'));
	}
	
	if(strlen($_POST['username']) <= 2) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => 'signup_username', "message" => 'Username is too short!'));
	}
	
	if($result['result'] == "error") { die(json_encode(array("reply" => $result))); }
	
	try{
		$stmt = DB::get()->dbh->prepare("SELECT count(*) AS NUM FROM ix_users WHERE username = :name");
		$stmt->bindParam(":name", $_POST['username'], PDO::PARAM_STR);
		$stmt->execute();
		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
	
	$fetch = $stmt->fetch();
	
	if($fetch->NUM > 0) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_username", "message" => "This username is already in use!"));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "user_signup") {
	
	$result = array("result" => "success",
					"data" => array());
	
	/**************************** signup checks *******************************/
	
	//blank checks
	if($_POST['username'] == "" || $_POST['username'] == null) { 
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_username", "message" => "Username can not be blank!"));
	}
	
	if($_POST['lastname'] == "" || $_POST['lastname'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_lastname", "message" => "User last name can not be blank!"));
	}
		
	if($_POST['firstname'] == "" || $_POST['firstname'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_firstname", "message" => "User first name can not be blank!"));
	}
	
	if(strlen($_POST['username']) <= 2) { 
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_username", "message" => "Username is too short!"));
	}
	
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

	if (preg_match($pattern, $_POST['email']) !== 1) {		
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_email", "message" => "Wrong user e-mail!"));		
	}
	
	try{
		$stmt = DB::get()->dbh->prepare("SELECT count(*) AS NUM FROM ix_users WHERE email = :email");
		$stmt->bindParam(":email", $_POST['email'], PDO::PARAM_STR);
		$stmt->execute();		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
	
	$ff = $stmt->fetch();
	
	if($ff->NUM > 0) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_email", "message" => "This e-mail already exists!"));
	}
	
	if($_POST['password'] != $_POST['password_repeat']) {
	  	$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_password", "message" => "Your passwords do not match!"));
		array_push($result['data'], array("field" => "signup_password_repeat", "message" => "Your passwords do not match!"));
	}	
	
	$strong = preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{6,16}$/', $_POST['password']);
	
	if(!$strong) {
	  	$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_password", "message" => "Your password is too weak!"));
		array_push($result['data'], array("field" => "signup_password_repeat", "message" => "Your password is too weak!"));
	}
	
	try {		
		$stmt = DB::get()->dbh->prepare("SELECT count(*) AS number FROM " . Config::read('mysql.prefix') . "users  WHERE username = :usr  ");
		$stmt->bindParam(":usr", $_POST['username'], PDO::PARAM_STR);		
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query error: " . $e->getMessage())));
	}
	
	$f = $stmt->fetch();
	
	if($f->number > 0) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "signup_username", "message" => "This username is already in use!"));
	}
	
	//at this point check if there is an error and return
	if($result['result'] == "error") {
		die(json_encode(array("reply" => $result)));
	}
	
	$password_salt = uniqid(mt_rand(), true);
	$password_hash = hash('sha256', ($password_salt . $_POST['password']));
	
	try {		
		$type = "new-user";
		$lvl = 1;
		
		$stmt = DB::get()->dbh->prepare("INSERT INTO " . Config::read('mysql.prefix') . "users  (username, password, salt, lastname, firstname, email, type, access_level) 
																						VALUES (:usn, :pwd, :pwdsalt, :lsnam, :firnam, :em, :typ, :alvl)  ");
		$stmt->bindParam(":usn", $_POST['username'], PDO::PARAM_STR);
		$stmt->bindParam(":pwd", $password_hash, PDO::PARAM_STR);
		$stmt->bindParam(":pwdsalt", $password_salt, PDO::PARAM_STR);
		$stmt->bindParam(":lsnam", $_POST['lastname'], PDO::PARAM_STR);
		$stmt->bindParam(":firnam", $_POST['firstname'], PDO::PARAM_STR);
		$stmt->bindParam(":em", $_POST['email'], PDO::PARAM_STR);
		$stmt->bindParam(":typ", $type, PDO::PARAM_STR);
		$stmt->bindParam(":alvl", $lvl, PDO::PARAM_INT);
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Signup query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "login_user") {
	
	$result = array("result" => "success",
					"data" => array());
	
	if($_POST['username'] == "" || $_POST['username'] == null) { 
		$result['result'] = "error";
		array_push($result['data'], array("field" => "login_username", "message" => "Username can not be blank!"));
	}
	
	if($_POST['password'] == "" || $_POST['password'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "login_password", "message" => "Password can not be blank!"));
	}
	
	if($result['result'] == "error") {
		die(json_encode(array("reply" => $result)));
	}
	
	try{
		$stmt = DB::get()->dbh->prepare("SELECT *, count(*) AS NUM FROM " . Config::read('mysql.prefix') . "users WHERE username = :name");
		$stmt->bindParam(":name", $_POST['username'], PDO::PARAM_STR);
		$stmt->execute();
		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
	
	$f = $stmt->fetch();
		
	if($f->NUM <= 0) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "login", "message" => "Wrong username or password!"));
		die(json_encode(array("reply" => $result)));
	}
	
	if($f->blocked === 1) {
		die(json_encode(array("error" => $f->blocked_message)));
	}
	
	$provided_pwd = $_POST['password'];
	$retrieved_pwd = $f->password;
	$salt = $f->salt;
	
	$pwd_to_be_tested = hash('sha256', ($salt . $provided_pwd));
			
	if($retrieved_pwd != $pwd_to_be_tested) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "login", "message" => "Wrong username or password!"));
		die(json_encode(array("reply" => $result)));
	}
	
	S('user-loggedIn', true);
	S('user-username', $f->username);
	
	$uniq = date("d") . "." . date("m") . "." . date("Y") . "_" .  __S('user-username');
	S('sessionUniqueId', $uniq);
	
	try{
		$stmt = DB::get()->dbh->prepare("UPDATE " . Config::read('mysql.prefix') . "users SET last_login = CURRENT_TIMESTAMP() WHERE username = :usr");
		$stmt->bindParam(":usr", $_POST['username'], PDO::PARAM_STR);
		$stmt->execute();
		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
			
	die(json_encode(array("reply" => $result)));
	
}

?>