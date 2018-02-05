<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['uri'] === "get_all_users") {
	
	if(!User::isAdmin(__S("user-username"))) { die(json_encode(array("error" => "You have to be an admin!"))); }
	
	$ret = User::getUsers();
	
	$result = array("result" => "success",
					"data" => $ret);
					
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "change_logged_users_password") {
	
	$result = array("result" => "success",
					"data" => array());

	if($_POST['username'] !== __S("user-username")) { die(json_encode(array("error" => "Do not try cheap tricks! Can only load logged users data!"))); }
	
	if($_POST['password'] != $_POST['password_repeat']) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "loggedUsersPanel #password-reset-input", "message" => "Your passwords do not match!"));
		array_push($result['data'], array("field" => "loggedUsersPanel #password-repeat-reset-input", "message" => "Your passwords do not match!"));
	}
	
	$strong = preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,16}$/', $_POST['password']);
	
	if(!$strong) {
	  	$result['result'] = "error";
		array_push($result['data'], array("field" => "loggedUsersPanel #password-reset-input", "message" => "Your password is too weak!"));
		array_push($result['data'], array("field" => "loggedUsersPanel #password-repeat-reset-input", "message" => "Your password is too weak!"));
	}
	
	if($result['result'] == "error") {
		die(json_encode(array("reply" => $result)));
	}
	
	$password_salt = uniqid(mt_rand(), true);
	$password_hash = hash('sha256', ($password_salt . $_POST['password']));
	
	try {		
				
		$stmt = DB::get()->dbh->prepare("UPDATE " . Config::read('mysql.prefix') . "users  SET password =:pwd, salt=:salt WHERE username=:usn");
		$stmt->bindParam(":usn", $_POST['username'], PDO::PARAM_STR);
		$stmt->bindParam(":pwd", $password_hash, PDO::PARAM_STR);
		$stmt->bindParam(":salt", $password_salt, PDO::PARAM_STR);		
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Change logged users password query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "load_logged_users_data") {

	if($_POST['username'] !== __S("user-username")) { die(json_encode(array("error" => "Do not try cheap tricks! Can only load logged users data!"))); }
	
	try{
		$stmt = DB::get()->dbh->prepare("SELECT * FROM " . Config::read('mysql.prefix') . "users WHERE username = :usr");
		$stmt->bindParam(":usr", $_POST['username'], PDO::PARAM_STR);
		$stmt->execute();		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
	
	$f = $stmt->fetch();
	
	$data = array(
		'username' => $f->username,
		'lastname' => $f->lastname,
		'firstname' => $f->firstname,
		'email' => $f->email,
		'type' => $f->type,
		'access_level' => $f->access_level,
		'last_login' => $f->last_login		
	);
	
	die(json_encode(array("success" => $data)));
	
}

if($_POST['uri'] == "logged_user_edit") {
	
	$result = array("result" => "success",
					"data" => array());
					
	if($_POST['username'] !== __S("user-username")) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "lastname", "message" => "You can only edit your information!"));
	}
	
	/**************************** signup checks *******************************/
		
	//blank checks		
	if($_POST['lastname'] == "" || $_POST['lastname'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "lastname", "message" => "User last name can not be blank!"));
	}
	
	if($_POST['firstname'] == "" || $_POST['firstname'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "firstname", "message" => "User first name can not be blank!"));
	}
	
	if($_POST['access_level'] < 1 || $_POST['access_level'] > 5) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "access-level", "message" => "Access level can take values between 1 and 5!"));
	}
		
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

	if (preg_match($pattern, $_POST['email']) !== 1) {		
		$result['result'] = "error";
		array_push($result['data'], array("field" => "email", "message" => "Invalid E-mail!"));		
	}
	
	try{
		$stmt = DB::get()->dbh->prepare("SELECT count(*) AS NUM FROM " . Config::read('mysql.prefix') . "users WHERE email = :email AND username != :usr");
		$stmt->bindParam(":email", $_POST['email'], PDO::PARAM_STR);
		$stmt->bindParam(":usr", $_POST['username'], PDO::PARAM_STR);
		$stmt->execute();		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
	
	$ff = $stmt->fetch();
	
	if($ff->NUM > 0) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "email", "message" => "This e-mail is already in use!"));
	}
		
	//at this point check if there is an error and return
	if($result['result'] == "error") {
		die(json_encode(array("reply" => $result)));
	}
			
	try {
		$stmt = DB::get()->dbh->prepare("UPDATE " . Config::read('mysql.prefix') . "users  SET lastname = :lsn, firstname = :fsn, email = :ema, access_level = :acclvl WHERE username = :usn ");		
		$stmt->bindParam(":lsn", $_POST['lastname'], PDO::PARAM_STR);
		$stmt->bindParam(":fsn", $_POST['firstname'], PDO::PARAM_STR);
		$stmt->bindParam(":ema", $_POST['email'], PDO::PARAM_STR);
		$stmt->bindParam(":usn", $_POST['username'], PDO::PARAM_STR);
		$stmt->bindParam(":acclvl", $_POST['access_level'], PDO::PARAM_STR);
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Edit logged user query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "edit_user") {	
	
	$result = array("result" => "success",
					"data" => array());
					
	if(!User::isAdmin(__S("user-username"))) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "lastname", "message" => "You must be an admin to do that!"));
	}
	
	/**************************** signup checks *******************************/
		
	//blank checks		
	if($_POST['lastname'] == "" || $_POST['lastname'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "lastname", "message" => "User last name can not be blank!"));
	}
	
	if($_POST['firstname'] == "" || $_POST['firstname'] == null) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "firstname", "message" => "User first name can not be blank!"));
	}
	
	if($_POST['access_level'] < 1 || $_POST['access_level'] > 5) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "access-level", "message" => "Access level can take values between 1 and 5!"));
	}
		
	$pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

	if (preg_match($pattern, $_POST['email']) !== 1) {		
		$result['result'] = "error";
		array_push($result['data'], array("field" => "email", "message" => "Invalid E-mail!"));		
	}
	
	try{
		$stmt = DB::get()->dbh->prepare("SELECT count(*) AS NUM FROM " . Config::read('mysql.prefix') . "users WHERE email = :email AND username != :usr");
		$stmt->bindParam(":email", $_POST['email'], PDO::PARAM_STR);
		$stmt->bindParam(":usr", $_POST['username'], PDO::PARAM_STR);
		$stmt->execute();		
	} catch(exception $e) {
		die(json_encode(array("error" => $e->getMessage())));
	}
	
	$ff = $stmt->fetch();
	
	if($ff->NUM > 0) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "email", "message" => "This e-mail is already in use!"));
	}
		
	//at this point check if there is an error and return
	if($result['result'] == "error") {
		die(json_encode(array("reply" => $result)));
	}
	
	$blocked = ( $_POST['blocked'] === 'true' ? (int)1 : (int)0 );
	$blocked_message = $_POST['blocked_message'];
	
	$loggedUser = User::getLoggedUser();
	
	$type = strtolower($_POST['type']);
	
	if(!User::isAdmin(__S("user-username")) && $loggedUser['type'] !== $type) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "type", "message" => "You can not do that unless you are an admin!"));
		die(json_encode(array("reply" => $result)));
	}
	
	if($_POST['username'] === __S("user-username") && User::isAdmin(__S("user-username")) && strtolower($_POST['type']) !== "administrator") {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "type", "message" => "You can not demote your users rights!"));
		die(json_encode(array("reply" => $result)));
	}
	
	try {
		$stmt = DB::get()->dbh->prepare("UPDATE " . Config::read('mysql.prefix') . "users  SET lastname = :lsn, firstname = :fsn, email = :ema, access_level = :acclvl, blocked = :blocked, blocked_message = :blMsg, type = :typ WHERE username = :usn ");		
		$stmt->bindParam(":lsn", $_POST['lastname'], PDO::PARAM_STR);
		$stmt->bindParam(":fsn", $_POST['firstname'], PDO::PARAM_STR);
		$stmt->bindParam(":ema", $_POST['email'], PDO::PARAM_STR);
		$stmt->bindParam(":usn", $_POST['username'], PDO::PARAM_STR);
		$stmt->bindParam(":acclvl", $_POST['access_level'], PDO::PARAM_STR);
		$stmt->bindParam(":blMsg", $blocked_message, PDO::PARAM_STR);
		$stmt->bindParam(":blocked", $blocked, PDO::PARAM_INT);
		$stmt->bindParam(":typ", $type, PDO::PARAM_STR);
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Edit logged user query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "delete_user") {
	
	$result = array("result" => "success",
					"data" => array());
	
	if(!User::isAdmin(__S("user-username"))) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "", "message" => "You must be an admin to do that!"));
	}
						
	if($_POST['username'] === __S("user-username")) {
		$result['result'] = "error";
		array_push($result['data'], array("field" => "", "message" => "You can not delete your own user!"));
		die(json_encode(array("reply" => $result)));
	}
	
	try {
		$stmt = DB::get()->dbh->prepare("DELETE FROM " . Config::read('mysql.prefix') . "users WHERE username = :usn ");	
		$stmt->bindParam(":usn", $_POST['username'], PDO::PARAM_STR);		
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Edit logged user query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "logout") {
	
	$result = array("result" => "success",
					"data" => array());
	
	if(!User::isLoggedIn()) {
	    
	    die(json_encode(array("error" => "User must be logged in to logout!")));
	    
	}
	
	User::logout();
	
	die(json_encode(array("success" => "User has successfully logged out!")));
	
}

?>