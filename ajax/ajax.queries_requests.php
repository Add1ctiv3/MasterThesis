<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['uri'] == "queryDatabase") {

    $LIMIT = 600;
	
	$result = array("result" => "success",
					"message" => "");

    $CALLER = $_POST['caller']; //check
    $CALLED = $_POST['called'];//check
    $AND_OR = $_POST['andOr'];//check
    $FROM_DATE = $_POST['dateFrom'];//check
    $TO_DATE = $_POST['dateTo'];//check
    $FROM_TIME = $_POST['timeFrom'];//check
    $TO_TIME = $_POST['timeTo'];//check
    $TYPE = $_POST['type']; //check
    $DURATION = $_POST['duration']; //check
    $FROM_WEIGHT = $_POST['weightFrom']; //check
    $TO_WEIGHT = $_POST['weightTo']; //check
    $FROM_INSERT_DATE = $_POST['insertFrom']; //check
    $TO_INSERT_DATE = $_POST['insertTo']; //check
    $SET_NAME = $_POST['inSet']; //check

    if(!isset($_POST['offSet']) || empty($_POST['offSet']) || is_int($_POST['offSet'])) {
        $OFFSET = 0;
    }else {
        $OFFSET = $_POST['offSet'];
    }

    $filter = false;

    /* ADD THESE IN THE SELECT STATEMENT TO INCLUDE TELEPHONE NUMBERS IN THE RESULTS
     *  TELEPHONE_1.number AS number_1,
        TELEPHONE_1.type AS type_1,
        TELEPHONE_2.number AS number_2,
        TELEPHONE_2.type AS type_2,
        TELEPHONE_1.country_code AS ccode_1,
        TELEPHONE_2.country_code AS ccode_2,
        TELEPHONE_1.creation_date AS creation_1,
        TELEPHONE_2.creation_date AS creation_2
     * */

    $query = "SELECT
                TEL.*                
              FROM 
                " . Config::read('mysql.prefix') . "telecommunications AS TEL
              ";

    if($SET_NAME != "" && $SET_NAME != null) {

        $query .= " JOIN " . Config::read('mysql.prefix') . "datasets_telecommunications AS ASSOC
              ON
                TEL.telephone_1 = ASSOC.caller AND TEL.telephone_2 = ASSOC.called AND TEL.time_stamp = ASSOC.time_stamp AND TEL.duration = ASSOC.duration ";

    }
/*  ADD THESE JOINS TO INCLUDE TELEPHONE NUMBERS IN THE RESULTS
    $query .= " JOIN 
                    " . Config::read('mysql.prefix') . "telephone_number AS TELEPHONE_1
                ON 
                    TEL.telephone_1 = TELEPHONE_1.number ";

    $query .= " JOIN 
                    " . Config::read('mysql.prefix') . "telephone_number AS TELEPHONE_2
                ON 
                    TEL.telephone_2 = TELEPHONE_2.number ";
*/
    $query .= " WHERE ";

    //if both caller and called are filled
    if(($CALLER != "" && $CALLER != null) && ($CALLED != "" && $CALLED != null))  {
        $query .= " (TEL.telephone_1 = '".$CALLER."' ";
        $query .= " ".$AND_OR." ";
        $query .= " TEL.telephone_2 = '".$CALLED."') AND ";
        $filter = true;
    } else {
        if($CALLER != "" && $CALLER != null) {
            $query .= " TEL.telephone_1 = '".$CALLER."' AND ";
            $filter = true;
        }

        if($CALLED != "" && $CALLED != null) {
            $query .= " TEL.telephone_2 = '".$CALLED."' AND ";
            $filter = true;
        }
    }

    if($FROM_DATE != "" && $FROM_DATE != null) {
        $query .= " TEL.time_stamp >= UNIX_TIMESTAMP(DATE('".intToSqlDate(dateToInt($FROM_DATE))."')) AND ";
        $filter = true;
    }

    if($TO_DATE != "" && $TO_DATE != null) {
        $query .= " TEL.time_stamp <= UNIX_TIMESTAMP(DATE('".intToSqlDate(dateToInt($TO_DATE))."')) AND ";
        $filter = true;
    }

    if($FROM_TIME != "" && $FROM_TIME != null) {
        $query .= " TIME(FROM_UNIXTIME(TEL.time_stamp)) >= TIME('".$FROM_TIME."') AND ";
        $filter = true;
    }

    if($TO_TIME != "" && $TO_TIME != null) {
        $query .= " TIME(FROM_UNIXTIME(TEL.time_stamp)) <= TIME('".$TO_TIME."') AND ";
        $filter = true;
    }

    if($TYPE != "" && $TYPE != null && ($TYPE == "CALL" || $TYPE == "SMS")) {
        $query .= " TEL.type = '".strtoupper($TYPE)."' AND ";
        $filter = true;
    }

    if($FROM_WEIGHT != "" && $FROM_WEIGHT != null && ($FROM_WEIGHT >= 0.1 && $FROM_WEIGHT <= 1)) {
        $query .= " TEL.weight >= ".$FROM_WEIGHT." AND ";
        $filter = true;
    }

    if($TO_WEIGHT != "" && $TO_WEIGHT != null && ($TO_WEIGHT >= 0.1 && $TO_WEIGHT <= 1)) {
        $query .= " TEL.weight <= ".$TO_WEIGHT." AND ";
        $filter = true;
    }

    if($FROM_INSERT_DATE != "" && $FROM_INSERT_DATE != null) {
        $query .= " DATE(TEL.creation_date) >= DATE('".intToSqlDate(dateToInt($FROM_INSERT_DATE))."') AND ";
        $filter = true;
    }

    if($TO_INSERT_DATE != "" && $TO_INSERT_DATE != null) {
        $query .= " DATE(TEL.creation_date) <= DATE('".intToSqlDate(dateToInt($TO_INSERT_DATE))."') AND ";
        $filter = true;
    }

    if($SET_NAME != "" && $SET_NAME != null) {

        $query .= " ASSOC.set_name = '".$SET_NAME."' AND ";
        $filter = true;

    }

    if($filter && $DURATION != "" && $DURATION != null && isTime($DURATION)) {
        $query .= " TEL.duration >= ".timeToInt($DURATION);
    } else {
        $query = substr($query, 0, strlen($query) - 4);
    }

    if($filter) {

        //first run a count query to check out the load! ONLY IF THE $_POST['total_records']  is empty
        if(!isset($_POST['total_records']) || empty($_POST['total_records'])) {
            $countQuery = "SELECT COUNT(*) as total_records FROM (".$query.") AS Q ";
            try {
                $stmt = DB::get()->dbh->prepare($countQuery);
                $stmt->execute();
            } catch(PDOException $e) {
                die(json_encode(array("error" => "Database countQuery failed: " . $e->getMessage())));
            }
            $total_records = $stmt->fetch()->total_records;
        } else {
            $total_records = $_POST['total_records'];
        }

        $query .= "
         LIMIT
            ".$LIMIT."
         OFFSET
            ".$OFFSET." 
        ";

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();
        } catch(PDOException $e) {
            die(json_encode(array("error" => "Database Query failed: " . $e->getMessage())));
        }

        $coms = array();
        //$numbers = array();  UNCOMMENT TO INCLUDE NUMBERS

        while($com = $stmt->fetch()) {

            array_push($coms, array(
                "caller" => $com->telephone_1,
                "called" => $com->telephone_2,
                "timestamp" => intToDateAndTime($com->time_stamp),
                "stamp" => $com->time_stamp,
                "duration" => $com->duration,
                "weight" => $com->weight,
                "type" => $com->type,
                "creation_date" => $com->creation_date,
                "record_type" => "telecommunication"
            ));

            /* UNCOMMENT TO INCLUDE NUMBERS
            if(!existsInArray($com->telephone_1, $numbers)) {
                array_push($numbers, array(
                    "number" => $com->telephone_1,
                    "type" => $com->type_1,
                    "country_code" => $com->ccode_1,
                    "creation" => $com->creation_1,
                    "record_type" => "telephone"
                ));
            }

            if(!existsInArray($com->telephone_2, $numbers)) {
                array_push($numbers, array(
                    "number" => $com->telephone_2,
                    "type" => $com->type_2,
                    "country_code" => $com->ccode_2,
                    "creation" => $com->creation_2,
                    "record_type" => "telephone"
                ));
            }*/

        }

        //$result['data'] = array_merge($numbers, $coms);  REPLACE THE BELOW LINE WITH THIS TO INCLUDE NUMBERS
        $result['data'] = $coms;
        $result['total_records'] = $total_records;
        $result['offset'] = $OFFSET;

    } else { //end of if filter block

        $result['result'] = "failure";
        $result['message'] = "You have not provided a filter value.";

    } //end of if not filter block

	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "query2Database") {

    $LIMIT = 600;

    $result = array("result" => "success",
        "message" => "");

    $number = $_POST['number'];
    $id_number = $_POST['id_number'];
    $surname = $_POST['surname'];
    $name = $_POST['name'];
    $fathersname = $_POST['fathersname'];
    $mothersname = $_POST['mothersname'];
    $birthdate = $_POST['birthdate'];
    $country = $_POST['country'];
    $ssn = $_POST['ssn'];
    $alias = $_POST['alias'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];

    if(!isset($_POST['offSet']) || empty($_POST['offSet']) || is_int($_POST['offSet'])) {
        $OFFSET = 0;
    }else {
        $OFFSET = $_POST['offSet'];
    }

    $filter = false;

    $query = "SELECT
                TEL.*
              FROM
                " . Config::read('mysql.prefix') . "telephone_number AS TEL
              LEFT JOIN
                " . Config::read('mysql.prefix') . "persons_telephones AS PT
              ON
                TEL.number = PT.telephone_number
              LEFT JOIN
                " . Config::read('mysql.prefix') . "persons AS P
              ON
                PT.id_number = P.id_number AND PT.surname = P.surname AND PT.name = P.name              
              ";

    if(isset($_POST['inSet']) && !empty($_POST['inSet'])) {

        $query .= " LEFT JOIN
                     " . Config::read('mysql.prefix') . "datasets_telephone_numbers AS DP
                   ON
                     TEL.number = DP.number ";

    }

    $query .= " WHERE ";

    $otherThanNumberFilter = false;

    if(!empty($number))  {
        $query .= " TEL.number = '".$number."' AND ";
        $filter = true;
    }

    if(!empty($id_number)) {
        $query .= " P.id_number LIKE '%".$id_number."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($surname)) {
        $query .= " P.surname LIKE '%".$surname."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($name)) {
        $query .= " P.name LIKE '%".$name."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($birthdate)) {
        $bd = intToSqlDate(dateToInt($birthdate));
        $query .= " P.birthdate = '".$bd."' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($country)) {
        $query .= " P.country = '".$country."' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($fathersname)) {
        $query .= " P.fathersname LIKE '%".$fathersname."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($mothersname)) {
        $query .= " P.mothersname LIKE '%".$mothersname."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($ssn)) {
        $query .= " P.ssn = '".$ssn."' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($alias)) {
        $query .= " P.alias LIKE '%".$alias."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($address)) {
        $query .= " P.address LIKE '%".$address."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if($gender != "not_selected") {
        $query .= " P.gender = '".$gender."' AND ";
        $filter = true;
    }

    if(isset($_POST['inSet']) && !empty($_POST['inSet'])) {
        $query .= " DP.set_name = '".$_POST['inSet']."' AND ";
        $filter = true;
    }

    $query = substr($query, 0, strlen($query) - 4);

    if($filter) {

        //first run a count query to check out the load! ONLY IF THE $_POST['total_records']  is empty
        if(!isset($_POST['total_records']) || empty($_POST['total_records'])) {
            $countQuery = "SELECT COUNT(*) as total_records FROM (".$query.") AS Q ";
            try {
                $stmt = DB::get()->dbh->prepare($countQuery);
                $stmt->execute();
            } catch(PDOException $e) {
                die(json_encode(array("error" => "Database countQuery failed: " . $e->getMessage())));
            }
            $total_records = $stmt->fetch()->total_records;
        } else {
            $total_records = $_POST['total_records'];
        }

        $query .= "
         LIMIT
            ".$LIMIT."
         OFFSET
            ".$OFFSET." 
        ";

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();
        } catch(PDOException $e) {
            die(json_encode(array("error" => "Database Query failed: " . $e->getMessage())));
        }

        $numbers = array();

        while($num = $stmt->fetch()) {

            array_push($numbers, array(
                "number" => $num->number,
                "type" => $num->type,
                "country_code" => !$num->country_code?"":$num->country_code,
                "creation" => $num->creation_date,
                "num_weight" => $num->weight,
                "record_type" => "telephone"
            ));

        }

        $result['data'] = $numbers;
        $result['total_records'] = $total_records;
        $result['offset'] = $OFFSET;

    } else { //end of if filter block

        $result['result'] = "failure";
        $result['message'] = "You have not provided a filter value.";

    } //end of if not filter block

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "associateQueryResultWithSet") {

    $result = array("result" => "success",
        "message" => "Your results have been associated with the selected set!");

    $CALLER = $_POST['caller']; //check
    $CALLED = $_POST['called'];//check
    $AND_OR = $_POST['andOr'];//check
    $FROM_DATE = $_POST['dateFrom'];//check
    $TO_DATE = $_POST['dateTo'];//check
    $FROM_TIME = $_POST['timeFrom'];//check
    $TO_TIME = $_POST['timeTo'];//check
    $TYPE = $_POST['type']; //check
    $DURATION = $_POST['duration']; //check
    $FROM_WEIGHT = $_POST['weightFrom']; //check
    $TO_WEIGHT = $_POST['weightTo']; //check
    $FROM_INSERT_DATE = $_POST['insertFrom']; //check
    $TO_INSERT_DATE = $_POST['insertTo']; //check
    $SET_NAME = $_POST['inSet']; //check

    if(!isset($_POST['set_to_associate_with']) || empty($_POST['set_to_associate_with'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to select a dataset to associate your query results with!";
        die(json_encode(array("reply" => $result)));
    }

    $SET_TO_PUT = $_POST['set_to_associate_with'];

    $filter = false;

    $query = "SELECT
                TEL.*           
              FROM 
                " . Config::read('mysql.prefix') . "telecommunications AS TEL
              ";

    if($SET_NAME != "" && $SET_NAME != null) {

        $query .= " JOIN " . Config::read('mysql.prefix') . "datasets_telecommunications AS ASSOC
              ON
                TEL.telephone_1 = ASSOC.caller AND TEL.telephone_2 = ASSOC.called AND TEL.time_stamp = ASSOC.time_stamp AND TEL.duration = ASSOC.duration ";

    }

    $query .= " JOIN 
                    " . Config::read('mysql.prefix') . "telephone_number AS TELEPHONE_1
                ON 
                    TEL.telephone_1 = TELEPHONE_1.number ";

    $query .= " JOIN 
                    " . Config::read('mysql.prefix') . "telephone_number AS TELEPHONE_2
                ON 
                    TEL.telephone_2 = TELEPHONE_2.number ";

    $query .= " WHERE ";

    //if both caller and called are filled
    if(($CALLER != "" && $CALLER != null) && ($CALLED != "" && $CALLED != null))  {
        $query .= " (TEL.telephone_1 = '".$CALLER."' ";
        $query .= " ".$AND_OR." ";
        $query .= " TEL.telephone_2 = '".$CALLED."') AND ";
        $filter = true;
    } else {
        if($CALLER != "" && $CALLER != null) {
            $query .= " TEL.telephone_1 = '".$CALLER."' AND ";
            $filter = true;
        }
        if($CALLED != "" && $CALLED != null) {
            $query .= " TEL.telephone_2 = '".$CALLED."' AND ";
            $filter = true;
        }
    }

    if($FROM_DATE != "" && $FROM_DATE != null) {
        $query .= " TEL.time_stamp >= UNIX_TIMESTAMP(DATE('".intToSqlDate(dateToInt($FROM_DATE))."')) AND ";
        $filter = true;
    }

    if($TO_DATE != "" && $TO_DATE != null) {
        $query .= " TEL.time_stamp <= UNIX_TIMESTAMP(DATE('".intToSqlDate(dateToInt($TO_DATE))."')) AND ";
        $filter = true;
    }

    if($FROM_TIME != "" && $FROM_TIME != null) {
        $query .= " TIME(FROM_UNIXTIME(TEL.time_stamp)) >= TIME('".$FROM_TIME."') AND ";
        $filter = true;
    }

    if($TO_TIME != "" && $TO_TIME != null) {
        $query .= " TIME(FROM_UNIXTIME(TEL.time_stamp)) <= TIME('".$TO_TIME."') AND ";
        $filter = true;
    }

    if($TYPE != "" && $TYPE != null && ($TYPE == "CALL" || $TYPE == "SMS")) {
        $query .= " TEL.type = '".strtoupper($TYPE)."' AND ";
        $filter = true;
    }

    if($FROM_WEIGHT != "" && $FROM_WEIGHT != null && ($FROM_WEIGHT >= 0.1 && $FROM_WEIGHT <= 1)) {
        $query .= " TEL.weight >= ".$FROM_WEIGHT." AND ";
        $filter = true;
    }

    if($TO_WEIGHT != "" && $TO_WEIGHT != null && ($TO_WEIGHT >= 0.1 && $TO_WEIGHT <= 1)) {
        $query .= " TEL.weight <= ".$TO_WEIGHT." AND ";
        $filter = true;
    }

    if($FROM_INSERT_DATE != "" && $FROM_INSERT_DATE != null) {
        $query .= " DATE(TEL.creation_date) >= DATE('".intToSqlDate(dateToInt($FROM_INSERT_DATE))."') AND ";
        $filter = true;
    }

    if($TO_INSERT_DATE != "" && $TO_INSERT_DATE != null) {
        $query .= " DATE(TEL.creation_date) <= DATE('".intToSqlDate(dateToInt($TO_INSERT_DATE))."') AND ";
        $filter = true;
    }

    if($SET_NAME != "" && $SET_NAME != null) {

        $query .= " ASSOC.set_name = '".$SET_NAME."' AND ";
        $filter = true;

    }

    if($filter && $DURATION != "" && $DURATION != null && isTime($DURATION)) {
        $query .= " TEL.duration >= ".timeToInt($DURATION);
    } else {
        $query = substr($query, 0, strlen($query) - 4);
    }

    if($filter) {

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();
        } catch(PDOException $e) {
            die(json_encode(array("error" => "Database Query failed: " . $e->getMessage())));
        }

        while($com = $stmt->fetch()) {

            $insertQuery = "INSERT IGNORE INTO ix_datasets_telecommunications (`set_name`, `caller`, `called`, `time_stamp`, `duration`) VALUES 
                              ('".$SET_TO_PUT."', '".$com->telephone_1."', '".$com->telephone_2."', '".$com->time_stamp."', ".$com->duration.");";

            $insertQuery2 = "INSERT IGNORE INTO ix_datasets_telephone_numbers (`set_name`, `number`) VALUES 
                              ('".$SET_TO_PUT."', '".$com->telephone_1."'), ('".$SET_TO_PUT."', '".$com->telephone_2."');";

            try {
                $stmt2 = DB::get()->dbh->prepare($insertQuery);
                $stmt2->execute();
            } catch(PDOException $e) {
                die(json_encode(array("error" => "Database associate results with set query failed: " . $e->getMessage())));
            }

            try {
                $stmt2 = DB::get()->dbh->prepare($insertQuery2);
                $stmt2->execute();
            } catch(PDOException $e) {
                die(json_encode(array("error" => "Database associate results with set query 2 failed: " . $e->getMessage())));
            }

        }

    } else { //end of if filter block

        $result['result'] = "failure";
        $result['message'] = "You have not provided a filter value.";

    } //end of if not filter block

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "associateQuery2ResultWithSet") {

    $result = array("result" => "success",
        "message" => "Your query results have been associated with the selected dataset!");

    $number = $_POST['number'];
    $id_number = $_POST['id_number'];
    $surname = $_POST['surname'];
    $name = $_POST['name'];
    $fathersname = $_POST['fathersname'];
    $mothersname = $_POST['mothersname'];
    $birthdate = $_POST['birthdate'];
    $country = $_POST['country'];
    $ssn = $_POST['ssn'];
    $alias = $_POST['alias'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];

    if(!isset($_POST['set_to_associate_with']) || empty($_POST['set_to_associate_with'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to select a dataset to associate your query results with!";
        die(json_encode(array("reply" => $result)));
    }

    $SET_TO_PUT = $_POST['set_to_associate_with'];

    $filter = false;

    $query = "SELECT
                TEL.*                
              FROM 
                " . Config::read('mysql.prefix') . "telephone_number AS TEL
              LEFT JOIN
                " . Config::read('mysql.prefix') . "persons_telephones AS PT
              ON
                TEL.number = PT.telephone_number
              LEFT JOIN
                " . Config::read('mysql.prefix') . "persons AS P
              ON
                PT.id_number = P.id_number AND PT.surname = P.surname AND PT.name = P.name
              LEFT JOIN
                " . Config::read('mysql.prefix') . "datasets_telephone_numbers AS DP
              ON
                TEL.number = DP.number
              WHERE
              ";

    $otherThanNumberFilter = false;

    if(!empty($number))  {
        $query .= " TEL.number = '".$number."' AND ";
        $filter = true;
    }

    if(!empty($id_number)) {
        $query .= " P.id_number LIKE '%".$id_number."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($surname)) {
        $query .= " P.surname LIKE '%".$surname."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($name)) {
        $query .= " P.name LIKE '%".$name."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($birthdate)) {
        $bd = intToSqlDate(dateToInt($birthdate));
        $query .= " P.birthdate = '".$bd."' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($country)) {
        $query .= " P.country = '".$country."' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($fathersname)) {
        $query .= " P.fathersname LIKE '%".$fathersname."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($mothersname)) {
        $query .= " P.mothersname LIKE '%".$mothersname."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($ssn)) {
        $query .= " P.ssn = '".$ssn."' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($alias)) {
        $query .= " P.alias LIKE '%".$alias."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if(!empty($address)) {
        $query .= " P.address LIKE '%".$address."%' AND ";
        $filter = true;
        $otherThanNumberFilter = true;
    }

    if($gender != "not_selected") {
        $query .= " P.gender = '".$gender."' AND ";
        $filter = true;
    }

    if(isset($_POST['inSet']) && !empty($_POST['inSet'])) {
        $query .= " DP.set_name = '".$_POST['inSet']."' AND ";
        $filter = true;
    }

    $query = substr($query, 0, strlen($query) - 4);

    if($filter) {

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();
        } catch(PDOException $e) {
            die(json_encode(array("error" => "Database Query failed: " . $e->getMessage())));
        }

        while($num = $stmt->fetch()) {

            $insertQuery = "INSERT IGNORE INTO ix_datasets_telephone_numbers (`set_name`, `number`) VALUES 
                              ('".$SET_TO_PUT."', '".$num->number."');";

            try {
                $stmt2 = DB::get()->dbh->prepare($insertQuery);
                $stmt2->execute();
            } catch(PDOException $e) {
                die(json_encode(array("error" => "Database associate results with set query failed: " . $e->getMessage())));
            }

        }


    } else { //end of if filter block

        $result['result'] = "failure";
        $result['message'] = "You have not provided a filter value.";

    } //end of if not filter block

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "getSetNames") {

    $result = array("result" => "success",
        "message" => "");

    $sets = Dataset::getAllDatasets();

    $ret = array();

    foreach($sets as $set) {

        array_push($ret, $set->getName());

    }

    $result['data'] = $ret;

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "deleteRecords") {

    $result = array("result" => "success",
        "message" => "",
        "data" => "");

    $records = $_POST["records"];

    foreach($records as $record) {

        if($record['record_type'] == "telecommunication") {
            try {
                $query = "DELETE FROM " . Config::read('mysql.prefix') . "telecommunications WHERE telephone_1 = :caller AND telephone_2 = :called AND time_stamp = :stamp AND duration = :dur";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":caller", $record['caller'], PDO::PARAM_STR);
                $stmt->bindParam(":called", $record['called'], PDO::PARAM_STR);
                $stmt->bindParam(":stamp", $record['timestamp'], PDO::PARAM_INT);
                $stmt->bindParam(":dur", $record['duration'], PDO::PARAM_INT);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Delete telecommunication query failed: " . $e->getMessage())));
            }
        }

        if($record['record_type'] == "telephone") {
            try {
                $query = "DELETE FROM " . Config::read('mysql.prefix') . "telephone_number WHERE number = :num ";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num", $record['number'], PDO::PARAM_STR);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Delete telephones query failed: " . $e->getMessage())));
            }
        }

    }

    $result['message'] = "The selected records have been deleted!";
    die(json_encode(array("reply"=>$result)));

}

if($_POST['uri'] == "newSet") {

    $result = array("result" => "success",
        "message" => ""
    );

    $username = __S("user-username");
    $name = $_POST['name'];
    $visibility= $_POST['type'];

    try {

        //first delete the filter
        $query = "INSERT INTO
					  " . Config::read('mysql.prefix') . "datasets
				  (name, creator_username, visibility)
				  VALUES 
				  (:nam, :creat, :visib)
					  ";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":nam", $name, PDO::PARAM_STR);
        $stmt->bindParam(":creat", $username, PDO::PARAM_STR);
        $stmt->bindParam(":visib", $visibility, PDO::PARAM_STR);
        $stmt->execute();

        $result['message'] = "Set has been created successfully.";

    } catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Your set has not been created...";
    }

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "associateRecordsWithSet") {

    $result = array("result" => "success",
        "message" => "",
        "data" => "");

    $records = $_POST["records"];
    $set = $_POST['set'];

    $query_coms = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telecommunications (set_name, caller, called, time_stamp, duration) VALUES ";

    $query_tels = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) VALUES ";

    $comsNumber = 0;
    $telsNumber = 0;

    foreach($records as $record) {

        if($record['record_type'] == "telephone") {
            $telsNumber++;
            $query_tels .= "('".$set."', '".$record['number']."'), ";
        }

        if($record['record_type'] == "telecommunication") {
            $comsNumber++;
            $telsNumber++;
            $telsNumber++;
            $query_coms .= "('".$set."', '".$record['caller']."', '".$record['called']."', ".$record['timestamp'].", ".$record['duration']."), ";
            $query_tels .= "('".$set."', '".$record['caller']."'), ";
            $query_tels .= "('".$set."', '".$record['called']."'), ";
        }

    }

    $query_coms = substr($query_coms, 0, strlen($query_coms)-2);
    $query_tels = substr($query_tels, 0, strlen($query_tels)-2);

    if($comsNumber > 0) {
        try {
            $stmt = DB::get()->dbh->prepare($query_coms);
            $stmt->execute();
        }
        catch(PDOException $e) {
            die(json_encode(array("error" => "Associate telecommunications with dataset query failed: " . $e->getMessage())));
        }
    }

    if($telsNumber > 0) {

        try {
            $stmt = DB::get()->dbh->prepare($query_tels);
            $stmt->execute();
        }
        catch(PDOException $e) {
            die(json_encode(array("error" => "Associate telephones with dataset query failed: " . $e->getMessage())));
        }
    }

    $result['message'] = "The selected records have been associated with the selected dataset!";
    die(json_encode(array("reply"=>$result)));

}

if($_POST['uri'] == "editRecord") {

    $result = array("result" => "success",
        "message" => "",
        "data" => "");

    $record = $_POST["record"];

    //telephone
    if($record['type']=="telephone") {

        $number = $record['number'];
        $num_weight = $record['num_weight'];
        $initialNumber = $record['initialNumber'];
        $countryCode = $record['country_code'];
        $type = $record['telType'];

        //check if the key is different
        if($number != $initialNumber) {

            try {
                $query = "SELECT COUNT(*) AS count FROM " . Config::read('mysql.prefix') . "telephone_number WHERE number = :num";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num", $number, PDO::PARAM_STR);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Check if this number key exists query failed: " . $e->getMessage())));
            }

            $q = $stmt->fetch();

            if($q->count > 0) {
                $result['result'] = "failure";
                $result['message'] = "The number you re trying to edit into already exists!";
                die(json_encode(array("reply" => $result)));
            }

            try {
                $query = "UPDATE " . Config::read('mysql.prefix') . "telephone_number SET country_code = :ccod, number = :numm, type=:typ, weight=:nwei WHERE number = :num";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num", $initialNumber, PDO::PARAM_STR);
                $stmt->bindParam(":numm", $number, PDO::PARAM_STR);
                $stmt->bindParam(":ccod", $countryCode, PDO::PARAM_STR);
                $stmt->bindParam(":typ", $type, PDO::PARAM_STR);
                $stmt->bindParam(":nwei", $num_weight, PDO::PARAM_INT);
                $stmt->execute();

                $result["message"] = "Your telephone number has been updated!";
                die(json_encode(array("reply" => $result)));
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Update telephone number country code and number query failed: " . $e->getMessage())));
            }

        } else { //the key hasn't changed

            try {
                $query = "UPDATE " . Config::read('mysql.prefix') . "telephone_number SET country_code = :ccod, type=:typ, weight=:nwei WHERE number = :num";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num", $number, PDO::PARAM_STR);
                $stmt->bindParam(":ccod", $countryCode, PDO::PARAM_STR);
                $stmt->bindParam(":typ", $type, PDO::PARAM_STR);
                $stmt->bindParam(":nwei", $num_weight, PDO::PARAM_INT);
                $stmt->execute();

                $result["message"] = "Your telephone number has been updated!";
                die(json_encode(array("reply" => $result)));
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Update telephone number country code query failed: " . $e->getMessage())));
            }

        }

    } //end of telephone block

    //telecommunication
    if($record['type'] == "telecommunication") {

        $caller = $record['caller'];
        $initialCaller = $record['initialCaller'];

        $called = $record['called'];
        $initialCalled = $record['initialCalled'];

        $stamp = dateAndTimeToInt($record['timestamp']);
        $initialStamp = dateAndTimeToInt($record['initialTimestamp']);

        $duration = $record['duration'];
        $initialDuration = $record['initialDuration'];

        $weight =  $record['weight'];

        $type = $record['comType'];

        if(!isDateAndTime($record['timestamp'])) {
            $result['result'] = "failure";
            $result['message'] = "Wrong date and time format!";
            die(json_encode(array("reply" => $result)));
        }

        if(!(is_numeric($record['duration']) && $record['duration'] >= 0)) {
            $result['result'] = "failure";
            $result['message'] = "Duration can only be a positive integer!";
            die(json_encode(array("reply" => $result)));
        }

        if(!(is_numeric($weight) && ($weight >= 0 && $weight <= 1))) {
            $result['result'] = "failure";
            $result['message'] = "Weight can vary between 0 and 1 (ex. 0.1, 0.4 etc) !";
            die(json_encode(array("reply" => $result)));
        }

        if($type != "CALL" && $type != "SMS" && $type != "OTHER") {
            $result['result'] = "failure";
            $result['message'] = "Record type can be CALL, SMS or OTHER!";
            die(json_encode(array("reply" => $result)));
        }

        //check if the key is the same
        if($caller == $initialCaller && $called == $initialCalled && $stamp == $initialStamp && $duration == $initialDuration) {

            try {
                $query = "UPDATE " . Config::read('mysql.prefix') . "telecommunications 
                    SET weight = :wei, type = :tp
                    WHERE telephone_1 = :num1 AND telephone_2 = :num2 AND time_stamp = :stm AND duration = :dur";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num1", $initialCaller, PDO::PARAM_STR);
                $stmt->bindParam(":num2", $initialCalled, PDO::PARAM_STR);
                $stmt->bindParam(":stm", $initialStamp, PDO::PARAM_INT);
                $stmt->bindParam(":dur", $initialDuration, PDO::PARAM_INT);
                $stmt->bindParam(":wei", $weight, PDO::PARAM_INT);
                $stmt->bindParam(":tp", $type, PDO::PARAM_STR);
                $stmt->execute();

                $result["message"] = "Your telecommunication has been updated!" . $nn->count;
                die(json_encode(array("reply" => $result)));
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Update telecommunication's weight/type query failed: " . $e->getMessage())));
            }

        } else { //if the key is different

            //check if the caller is different
            if($caller != $initialCaller) {
                try {
                    //try to insert the "new" number in the database if it doesn't exist
                    $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "telephone_number (number) VALUES (:num)";
                    $stmt = DB::get()->dbh->prepare($query);
                    $stmt->bindParam(":num", $caller, PDO::PARAM_STR);
                    $stmt->execute();

                    //try to associate the "new" number with this dataset
                    $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) VALUES (:setname, :num)";
                    $stmt = DB::get()->dbh->prepare($query);
                    $stmt->bindParam(":num", $record['set'], PDO::PARAM_STR);
                    $stmt->bindParam(":num", $caller, PDO::PARAM_STR);
                    $stmt->execute();
                }
                catch(PDOException $e) {
                    die(json_encode(array("error" => "Associate the number key with the dataset query failed: " . $e->getMessage())));
                }
            }

            if($called != $initialCalled) {
                try {
                    //try to insert the "new" number in the database if it doesnt exist
                    $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "telephone_number (number) VALUES (:num)";
                    $stmt = DB::get()->dbh->prepare($query);
                    $stmt->bindParam(":num", $called, PDO::PARAM_STR);
                    $stmt->execute();

                    //try to associate the "new" number with this dataset
                    $query = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) VALUES (:setname, :num)";
                    $stmt = DB::get()->dbh->prepare($query);
                    $stmt->bindParam(":num", $record['set'], PDO::PARAM_STR);
                    $stmt->bindParam(":num", $called, PDO::PARAM_STR);
                    $stmt->execute();
                }
                catch(PDOException $e) {
                    die(json_encode(array("error" => "Associate the number key with the dataset query failed: " . $e->getMessage())));
                }
            }

            try {
                $query = "SELECT COUNT(*) AS count FROM " . Config::read('mysql.prefix') . "telecommunications 
                WHERE telephone_1 = :num1 AND telephone_2 = :num2 AND time_stamp = :stm AND duration = :dur";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num1", $caller, PDO::PARAM_STR);
                $stmt->bindParam(":num2", $called, PDO::PARAM_STR);
                $stmt->bindParam(":stm", $stamp, PDO::PARAM_INT);
                $stmt->bindParam(":dur", $duration, PDO::PARAM_INT);
                $stmt->execute();
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Check if this telecommunication's key exists query failed: " . $e->getMessage())));
            }

            $q = $stmt->fetch();

            if($q->count > 0) {
                $result['result'] = "failure";
                $result['message'] = "The telecommunication you re trying to edit into, already exists!";
                die(json_encode(array("reply" => $result)));
            }

            try {
                $query = "UPDATE " . Config::read('mysql.prefix') . "telecommunications SET telephone_1 = :num1, telephone_2 = :num2, time_stamp = :stm, duration = :dur, weight = :wei, type = :tp
                    WHERE telephone_1 = :num3 AND telephone_2 = :num4 AND time_stamp = :stm2 AND duration = :dur2";
                $stmt = DB::get()->dbh->prepare($query);
                $stmt->bindParam(":num1", $caller, PDO::PARAM_STR);
                $stmt->bindParam(":num2", $called, PDO::PARAM_STR);
                $stmt->bindParam(":stm", $stamp, PDO::PARAM_INT);
                $stmt->bindParam(":dur", $duration, PDO::PARAM_INT);
                $stmt->bindParam(":num3", $initialCaller, PDO::PARAM_STR);
                $stmt->bindParam(":num4", $initialCalled, PDO::PARAM_STR);
                $stmt->bindParam(":stm2", $initialStamp, PDO::PARAM_INT);
                $stmt->bindParam(":dur2", $initialDuration, PDO::PARAM_INT);
                $stmt->bindParam(":wei", $weight, PDO::PARAM_STR);
                $stmt->bindParam(":tp", $type, PDO::PARAM_STR);
                $stmt->execute();

                $result["message"] = "Your telecommunication has been updated!";
                die(json_encode(array("reply" => $result)));
            }
            catch(PDOException $e) {
                die(json_encode(array("error" => "Update telephone number country code and number query failed: " . $e->getMessage())));
            }

        }

    }//end of telecommunication block

    $result['message'] = "The selected records have been removed from your dataset!";
    die(json_encode(array("reply"=>$result)));

}

if($_POST['uri'] == "getTelephonesAssociations") {

    $result = array("result" => "success",
        "message" => "",
        "data" => "");

    $number = $_POST['number'];
    $associations = array();

    $query = "SELECT
                  TEL.number, PER.*, ASSOC.relationship, ASSOC.validity
              FROM 
                  " . Config::read('mysql.prefix') . "telephone_number AS TEL
              JOIN
                  " . Config::read('mysql.prefix') . "persons_telephones AS ASSOC
              ON
                  TEL.number = ASSOC.telephone_number
              JOIN
                  " . Config::read('mysql.prefix') . "persons AS PER
              ON
                  ASSOC.surname = PER.surname AND ASSOC.name = PER.name AND ASSOC.id_number = PER.id_number
              WHERE
                  TEL.number = :num
              ";

    try{

        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":num", $number, PDO::PARAM_STR);
        $stmt->execute();

        if($stmt->rowCount() == 0) {
            $result['data'] = null;
            die(json_encode(array("reply" => $result)));
        }

        while($rec = $stmt->fetch()) {

            $assoc = array("id_number" => $rec->id_number ,
                           "surname" => $rec->surname ,
                           "name" => $rec->name ,
                           "birthdate" => ($rec->birthdate==null?"":$rec->birthdate) ,
                           "country" => ($rec->country==null?"":$rec->country) ,
                           "fathersname" => ($rec->fathersname==null?"":$rec->fathersname) ,
                           "mothersname" => ($rec->mothersname==null?"":$rec->mothersname) ,
                           "ssn" => ($rec->ssn==null?"":$rec->ssn) ,
                           "alias" => ($rec->alias==null?"":$rec->alias) ,
                           "gender" => ($rec->gender==null?"":$rec->gender) ,
                           "address" => ($rec->address==null?"":$rec->address),
                           "relationship" => ($rec->relationship==null?"":$rec->relationship),
                           "validity" => ($rec->validity==null?"":$rec->validity)
                          );

            array_push($associations, $assoc);

        }

        $result['data'] = $associations;

        die(json_encode(array("reply" => $result)));

    }catch(PDOException $e) {

        die(json_encode(array("error" => "Get telephone numbers associations query failed: ".$e->getMessage())));

    }

}

if($_POST['uri'] == "deleteTelephonePersonAssociation") {

    $result = array("result" => "success",
        "message" => "");

    $number = $_POST['telephone'];
    $relationship = $_POST['relationship'];
    $id_number = $_POST['id_number'];
    $surname = $_POST['surname'];
    $name = $_POST['name'];

    $query = "
        DELETE FROM
            ix_persons_telephones                            
        WHERE
            telephone_number = '".$number."' AND relationship = '".$relationship."' AND id_number = '".$id_number."' AND surname = '".$surname."' AND name = '".$name."'
    ";

    $orphansQuery = "
        DELETE FROM
            ix_persons        
        WHERE
            ( id_number, surname, name ) 
        NOT IN
            (SELECT id_number, surname, name FROM ix_persons_telephones WHERE TRUE)
    ";

    try{
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->execute();
    }catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Delete person telephone association query failed: " . $e->getMessage();
        die(json_encode(array("reply" => $result)));
    }

    try{
        $stmt = DB::get()->dbh->prepare($orphansQuery);
        $stmt->execute();
    }catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Delete orphan persons query failed: " . $e->getMessage();
        die(json_encode(array("reply" => $result)));
    }

    $result['message'] = "Person-Telephone association deleted!";
    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "editTelephonePersonAssociation") {

    $result = array("result" => "success",
        "message" => "",
        "data" => "");

    $number = $_POST['telephone'];
    $id_number = $_POST['id_number'];
    $surname = $_POST['surname'];
    $name = $_POST['name'];

    $fathersname = $_POST['fathersname'];
    $fatherSQL = "";
    if($fathersname == "") {
        $fatherSQL = "NULL";
    } else {
        $fatherSQL = "'" . $fathersname . "'";
    }

    $mothersname = $_POST['mothersname'];
    $motherSQL = "";
    if($mothersname == "") {
        $motherSQL = "NULL";
    } else {
        $motherSQL = "'" . $mothersname . "'";
    }

    $birthdate = $_POST['birthdate'];
    $birthSQL = "";
    if($birthdate == "") {
        $birthSQL = "NULL";
    } else {
        $birthSQL = "'" . intToSqlDate(dateToInt($birthSQL)) . "'";
    }

    $country = $_POST['country'];
    $countrySQL = "";
    if($country == "") {
        $countrySQL = "NULL";
    } else {
        $countrySQL = "'" . $country . "'";
    }

    $ssn = $_POST['ssn'];
    $ssnSQL = "";
    if($ssn == "") {
        $ssnSQL = "NULL";
    } else {
        $ssnSQL = "'" . $ssn . "'";
    }

    $gender = $_POST['gender'];
    $genderSQL = "";
    if($gender == "") {
        $genderSQL = "NULL";
    } else {
        $genderSQL = "'" . $gender . "'";
    }

    $alias = $_POST['alias'];
    $aliasSQL = "";
    if($alias == "") {
        $aliasSQL = "NULL";
    } else {
        $aliasSQL = "'" . $alias . "'";
    }

    $address = $_POST['address'];
    $addrSQL = "";
    if($address == "") {
        $addrSQL = "NULL";
    } else {
        $addrSQL = "'" . $address . "'";
    }

    $validity = $_POST['validity'];
    $validSQL = "";
    if($validity == "") {
        $validSQL = "NULL";
    } else {
        $validSQL = "'" . $validity . "'";
    }

    $relationship = $_POST['relationship'];

    $personQuery = "UPDATE 
                  ix_persons
              SET
                  mothersname = ".$motherSQL.",
                  fathersname = ".$fatherSQL.",
                  address = ".$addrSQL.",
                  birthdate = ".$birthSQL.",
                  alias = ".$aliasSQL.", 
                  gender = ".$genderSQL.",
                  country = ".$countrySQL.",
                  ssn = ".$ssnSQL."
              WHERE
                  id_number = '".$id_number."' AND surname = '".$surname."' AND name = '".$name."' 
              ";

    $associationQuery = "
                            UPDATE
                                ix_persons_telephones
                            SET
                                validity = ".$validSQL."
                            WHERE
                                telephone_number = '".$number."' AND relationship = '".$relationship."' AND id_number = '".$id_number."' AND surname = '".$surname."' AND name = '".$name."'
    ";

    try{
        $stmt = DB::get()->dbh->prepare($personQuery);
        $stmt->execute();
    }catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Update person query failed: " . $e->getMessage();
        die(json_encode(array("reply" => $result)));
    }

    try{
        $stmt = DB::get()->dbh->prepare($associationQuery);
        $stmt->execute();
    }catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Update person-telephone query failed: " . $e->getMessage();
        die(json_encode(array("reply" => $result)));
    }

    $result['message'] = "Your telephone - person association has been updated!";
    die(json_encode(array("reply" => $result)));

}

function existsInArray($number, $array) {

    foreach($array as $record) {
        if($record['record_type'] == "telephone" && $record['number'] == $number) {
            return true;
        }
    }

    return false;

}

?>