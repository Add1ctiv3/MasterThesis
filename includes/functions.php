<?php



if (!defined('__BOOTFILE__')) { die("Direct access is not allowed!"); }



/**

* SESSION FUNCTIONS 

*/



// Set/Update a session

function S($SESSION_NAME, $SESSION_VALUE) {

    $_SESSION[Config::read("session.prefix") . $SESSION_NAME] = $SESSION_VALUE;

}



// Print a session's value

function _S($SESSION_NAME) {

    echo $_SESSION[Config::read("session.prefix") . $SESSION_NAME];

}



// Return a session's value

function __S($SESSION_NAME) {

    return $_SESSION[Config::read("session.prefix") . $SESSION_NAME];

}



// Unset a session's value

function DS($SESSION_NAME) {

    unset($_SESSION[Config::read("session.prefix") . $SESSION_NAME]);

}



// Return current datetime in seconds

function Now() {

    return mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));

}



// Generate a random token

function generateToken() {

    mt_srand((double)microtime() * 1000000);

    

    return md5(mt_rand(1000000, 9999999));

}



function isDate($DATE) {

    if (!preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4}$/",trim($DATE))) { return false; }

    $DATE = explode("/", trim($DATE));

    if ($DATE[0] < 1 || $DATE[0] > 31) { return false; }

    if ($DATE[1] < 1 || $DATE[1] > 12) { return false; }

    if ($DATE[2] < 1900 || $DATE[2] > (int)date("Y")) { return false; }

    return true;

}

function isTime($TIME) {

	if(!preg_match("/^[0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/", trim($TIME)) && !preg_match("/^[0-9]{1,2}:[0-9]{1,2}$/", trim($TIME))) { return false; }

	$TIME = explode(":", trim($TIME));

	if($TIME[0] < 0) { return false; }

	if($TIME[1] < 0 || $TIME[1] > 60) { return false; }

	if($TIME[2] != null && $TIME[2] != "") {

		if($TIME[2] < 0 || $TIME[2] > 60) { return false; }

	}

	return true;
}

function isDateAndTime($val) {
    if (!preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/",trim($val))) { return false; }
    return true;
}

function intToTime($int) {

	$hours = intval($int/3600);

	$minutes = intval(($int - $hours*3600)/60);

	$seconds = $int - $hours*3600 - $minutes*60;
	

	if($hours < 10) { $hours = "0" . $hours; }

	if($minutes < 10) { $minutes = "0" . $minutes; }

	if($seconds < 10) { $seconds = "0" . $seconds; }

	return $hours . ":" . $minutes . ":" . $seconds;

}

function timeToInt($val) {
	$parts = explode(":", $val);
	return ($parts[0]*3600 + $parts[1]*60 + $parts[2]);
}

function dateToInt($DATE) {

	$DATE = str_replace("-", "/", $DATE);

	if (preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2}$/", trim($DATE))) {
		$HOUR_TIME = explode(" ", trim($DATE));
		$DATE = explode("/", $HOUR_TIME[0]);
		$TIME = explode(":", $HOUR_TIME[1]);
		if($TIME[2] == null || $TIME[2] == "") { $TIME[2] = 0; }
		return mktime($TIME[0], $TIME[1], $TIME[2], $DATE[1], $DATE[0], $DATE[2]);//array($TIME[0], $TIME[1], $TIME[2], $DATE[1], $DATE[0], $DATE[2]);
	}
	if(preg_match("/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/", trim($DATE))) {
		$DATEar = explode("/", trim($DATE));
		return mktime(0, 0, 0, $DATEar[1], $DATEar[0], $DATEar[2]);
	}
	return 0;
}

function intToDateAndTime($int) {
	return date("d/m/Y H:i:s", $int);
}

function intToDateOnly($int) {
    $int = $int +1 -1 ;
	return date("d/m/Y", $int);
}

function intToSqlDate($val) {
	return date("Y-m-d", $val);
}

function intToSqlDateAndTime($val) {
	return date("Y-m-d H:i:s", $val);
}

function startsWith($string, $value) {
	return $value === "" || strpos($string, $value) === 0;
}

function endsWith($string, $value) {
	return $value === "" || substr($string, -strlen($value)) === $value;
}

function contains($string, $value) {
	if (strpos($string, $value) !== false) {
		return true;
	}
	return false;
}

function dateAndTimeToInt($dnt) {
    return strtotime(str_replace('/', '-', $dnt));
}

?>