<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['uri'] == "importCall") {
	
	$result = array("result" => "success",
					"message" => "");

	if(!isset($_POST['caller']) || empty($_POST['caller'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to provide a caller for the telecommunication!";
        die(json_encode(array("reply" => $result)));
    }

    if(!isset($_POST['called']) || empty($_POST['called'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to provide a called number for the telecommunication!";
        die(json_encode(array("reply" => $result)));
    }

    if(!isset($_POST['timestamp']) || empty($_POST['timestamp'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to provide a date and time for the telecommunication!";
        die(json_encode(array("reply" => $result)));
    }

    if(!isset($_POST['duration']) || empty($_POST['duration'])) {
	    $result['result'] = "failure";
	    $result['message'] = "You need to provide a duration for the telecommunication!";
        die(json_encode(array("reply" => $result)));
    }

	$caller = $_POST['caller'];
	$called = $_POST['called'];
	$timestamp = dateToInt($_POST['timestamp']);
	$duration = timeToInt($_POST['duration']);

	$stampParts = explode(" ", $_POST['timestamp']);
	$date = $stampParts[0];
	$time = $stampParts[1];

	$weight = isset($_POST['weight']) ? $_POST['weight'] : null ;
	$type = isset($_POST['type']) ? strtoupper($POST['type']) : null ;

	$tel1 = new Telephone($caller);
	$tel2 = new Telephone($called);

	$com = new Telecommunication($tel1, $tel2, $date, $time, $duration);

	if(!$tel1->isValid()) {
        $result['result'] = "failure";
        $result['message'] = "Invalid caller! Reason: " . $tel1->getValidity();
        die(json_encode(array("reply" => $result)));
    }

    if(!$tel2->isValid()) {
        $result['result'] = "failure";
        $result['message'] = "Invalid called number! Reason: " . $tel2->getValidity();
        die(json_encode(array("reply" => $result)));
    }

    if(!$com->isValid()) {
        $result['result'] = "failure";
        $result['message'] = "Invalid telecommunication! Reason: " . $com->getValidity();
        die(json_encode(array("reply" => $result)));
    }

    try {

        $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "telephone_number (`number`) 
                VALUES (:c1), (:c2)";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":c1", $caller, PDO::PARAM_STR);
        $stmt->bindParam(":c2", $called, PDO::PARAM_STR);
        $stmt->execute();
    }
    catch(PDOException $e) {
        die(json_encode(array("error" => "Import telephone numbers query failed: " . $e->getMessage())));
    }

    try {

        $query = "INSERT INTO " . Config::read('mysql.prefix') . "telecommunications (`telephone_1`, `telephone_2`, `time_stamp`, `duration`, `weight`, `type`) 
                VALUES (:c1, :c2, :t, :d, :w, :ty)";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":c1", $caller, PDO::PARAM_STR);
        $stmt->bindParam(":c2", $called, PDO::PARAM_STR);
        $stmt->bindParam(":t", $timestamp, PDO::PARAM_INT);
        $stmt->bindParam(":d", $duration, PDO::PARAM_INT);
        $stmt->bindParam(":w", $weight, PDO::PARAM_STR);
        $stmt->bindParam(":ty", $type, PDO::PARAM_STR);
        $stmt->execute();

        $result['message'] = "Telecommunication imported successfully!";

    }
    catch(PDOException $e) {
	    if($e->getCode() == "23000") {
	        $result['result'] = "failure";
	        $result["message"] = "The telecommunication you are trying to import already exists!";

        } else {
            die(json_encode(array("error" => "Import telecommunication query failed: " . $e->getMessage())));
        }
    }

	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "importTelephonePerson") {

    $result = array("result" => "success",
        "message" => "You have successfully imported this number with/without its associated person!");

    if(!isset($_POST['number']) || empty($_POST['number'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to provide a telephone number!";
        die(json_encode(array("reply" => $result)));
    }

    $filledInPersonData = false;
    $validPersonKeyFields = false;

    if(
    (isset($_POST['id']) && !empty($_POST['id'])) ||
    (isset($_POST['surname']) && !empty($_POST['id'])) ||
    (isset($_POST['name']) && !empty($_POST['id'])) ||
    (isset($_POST['fathersname']) && !empty($_POST['id'])) ||
    (isset($_POST['mothersname']) && !empty($_POST['id'])) ||
    (isset($_POST['birthdate']) && !empty($_POST['id'])) ||
    (isset($_POST['country']) && !empty($_POST['id'])) ||
    (isset($_POST['address']) && !empty($_POST['id'])) ||
    (isset($_POST['ssn']) && !empty($_POST['id'])) ||
    (isset($_POST['alias']) && !empty($_POST['id'])) ||
    (isset($_POST['gender']) && $_POST['gender'] != 'not_selected')
        ) {

        $filledInPersonData = true;

        //now we check if the key fields are offered
        if(isset($_POST['id']) && !empty($_POST['id']) &&
            isset($_POST['surname']) && !empty($_POST['surname']) &&
            isset($_POST['name']) && !empty($_POST['name']) ) {

            $validPersonKeyFields = true;

        }
    }

    $number = $_POST['number'];

    $n = new Telephone($number);
    if(!$n->isValid()) {
        $result['result'] = "failure";
        $result['message'] = "Invalid telephone number! Reason: " . $n->getValidity();
        die(json_encode(array("reply" => $result)));
    }

    if($filledInPersonData && !$validPersonKeyFields) {
        $result['result'] = "failure";
        $result['message'] = "You need to provide at least an id number, a surname and a name for the associated person!";
        die(json_encode(array("reply" => $result)));
    }

    try {
        $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "telephone_number (`number`) 
                VALUES (:c1)";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":c1", $number, PDO::PARAM_STR);
        $stmt->execute();
    }
    catch(PDOException $e) {
        die(json_encode(array("error" => "Import telephone number query failed: " . $e->getMessage())));
    }

    if($filledInPersonData && $validPersonKeyFields) {

        $id = $_POST['id'];
        $surname = $_POST['surname'];
        $name = $_POST['name'];
        $fathersname = isset($_POST['fathersname']) ? $_POST['fathersname'] : null;
        $mothersname = isset($_POST['mothersname']) ? $_POST['mothersname'] : null;
        $birthdate = isset($_POST['birthdate']) ? intToSqlDate(dateToInt($_POST['birthdate'])) : null;
        $country = isset($_POST['country']) ? $_POST['country'] : null;
        $address = isset($_POST['address']) ? $_POST['address'] : null;
        $alias = isset($_POST['alias']) ? $_POST['alias'] : null;
        $ssn = isset($_POST['ssn']) ? $_POST['ssn'] : null;
        $gender = $_POST['gender']!="not_selected" ? $_POST['gender'] : "UNKNOWN";

        $p = new Person();
        $p->setIdNum($id);
        $p->setSurname($surname);
        $p->setName($name);
        $p->setBirthdate($birthdate);
        $p->setFathername($fathersname);
        $p->setMothername($mothersname);
        $p->setCountry($country);
        $p->setAddress($address);
        $p->setAlias($alias);
        $p->setSSN($ssn);
        $p->setGender($gender);

        if(!$p->isValid()) {
            $result['result'] = "failure";
            $result['message'] = "Invalid person data! Reason: " . $p->getValidity();
            die(json_encode(array("reply" => $result)));
        }

        try {
            $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "persons 
                    (`id_number`, `surname`, `name`, `fathersname`, `mothersname`, `birthdate`, `country`, `address`, `ssn`, `alias`, `gender`) 
                VALUES (:idn, :sur, :nam, :fnam, :mnam, :bd, :c, :addr, :ssn, :alia, :gen)";
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->bindParam(":idn", $id, PDO::PARAM_STR);
            $stmt->bindParam(":sur", $surname, PDO::PARAM_STR);
            $stmt->bindParam(":nam", $name, PDO::PARAM_STR);
            $stmt->bindParam(":fnam", $fathersname, PDO::PARAM_STR);
            $stmt->bindParam(":mnam", $mothersname, PDO::PARAM_STR);
            $stmt->bindParam(":bd", $birthdate, PDO::PARAM_STR);
            $stmt->bindParam(":c", $country, PDO::PARAM_STR);
            $stmt->bindParam(":addr", $address, PDO::PARAM_STR);
            $stmt->bindParam(":ssn", $ssn, PDO::PARAM_STR);
            $stmt->bindParam(":alia", $alias, PDO::PARAM_STR);
            $stmt->bindParam(":gen", $gender, PDO::PARAM_STR);
            $stmt->execute();
        }
        catch(PDOException $e) {
            die(json_encode(array("error" => "Import person query failed: " . $e->getMessage())));
        }

        try {
            $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "persons_telephones
                    (`telephone_number`, `id_number`, `surname`, `name`) 
                VALUES (:num, :idn, :sur, :nam)";
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->bindParam(":num", $number, PDO::PARAM_STR);
            $stmt->bindParam(":idn", $id, PDO::PARAM_STR);
            $stmt->bindParam(":sur", $surname, PDO::PARAM_STR);
            $stmt->bindParam(":nam", $name, PDO::PARAM_STR);
            $stmt->execute();
        }
        catch(PDOException $e) {
            die(json_encode(array("error" => "Import person-telephone association query failed: " . $e->getMessage())));
        }

    }

    die(json_encode(array("reply" => $result)));

}


?>