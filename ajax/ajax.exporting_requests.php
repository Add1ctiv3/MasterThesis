<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['uri'] == "exportSet") {
	
	$result = array("result" => "success",
					"message" => "Data successfully exported to your file!");

    if(!isset($_POST['set']) || empty($_POST['set'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to select a dataset to export!";
        die(json_encode(array("reply" => $result)));
    }

    if(!isset($_POST['filename']) || empty($_POST['filename'])) {
        $result['result'] = "failure";
        $result['message'] = "You need to provide a filename!";
        die(json_encode(array("reply" => $result)));
    }

    //build the query
    $coms_query = "SELECT
                T.*
              FROM
                ix_datasets_telecommunications AS DT
              JOIN
                ix_telecommunications AS T
              ON
                DT.caller = T.telephone_1 AND DT.called = T.telephone_2 AND DT.time_stamp = T.time_stamp AND DT.duration = T.duration
              WHERE
                DT.set_name = '".$_POST['set']."'
              ";

    $telephones_query = "
                SELECT
                  T.*
                FROM
                  ix_datasets_telephone_numbers AS DT
                JOIN
                  ix_telephone_number AS T
                ON 
                  DT.number = T.number
                WHERE
                  DT.set_name = '".$_POST['set']."'
    ";

    //first count telecommunications
    $countComsQuery = "
                    SELECT COUNT(*) AS number FROM (".$coms_query.") AS Q
    ";
    try{
        $stmt = DB::get()->dbh->prepare($countComsQuery);
        $stmt->execute();
    }catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Count communications query failed! " . $e->getMessage();
        die(json_encode(array("reply" => $result)));
    }

    $f = $stmt->fetch();
    $coms_number = $f->number;

    //then count telephone numbers
    $countTelephonesQuery = "
                    SELECT COUNT(*) AS number FROM (".$telephones_query.") AS Q
    ";
    try{
        $stmt = DB::get()->dbh->prepare($countTelephonesQuery);
        $stmt->execute();
    }catch(PDOException $e) {
        $result['result'] = "failure";
        $result['message'] = "Count telephone numbers query failed! " . $e->getMessage();
        die(json_encode(array("reply" => $result)));
    }

    $f = $stmt->fetch();
    $telephones_number = $f->number;

    if($coms_number == 0 && $telephones_number == 0) {
        $result['result'] = "failure";
        $result['message'] = "The selected dataset is empty!";
        die(json_encode(array("reply" => $result)));
    }

    //if we proceed it means there are records to be exported

    $filename = sanitize($_POST['filename'], true, true);

    if($filename == "" || !$filename) {
        $result['result'] = "failure";
        $result['message'] = "Please provide an appropriate file name!";
        die(json_encode(array("reply" => $result)));
    }

    $filename .= ".csv";

    //telecoms
    if($coms_number != 0) {

        $header = "caller;called;time_stamp;duration;weight;type\r\n";
        file_put_contents(ROOT_PATH . "/tempExportFiles/" . $filename, $header, FILE_APPEND);

        //run the query
        try{
            $stmt = DB::get()->dbh->prepare($coms_query);
            $stmt->execute();
        }catch(PDOException $e) {
            $result['result'] = "failure";
            $result['message'] = "Telecommunications query failed! " . $e->getMessage();
            die(json_encode(array("reply" => $result)));
        }

        //records loop
        while($com = $stmt->fetch()) {

            $line = $com->telephone_1 . ";" . $com->telephone_2 . ";" . intToDateAndTime($com->time_stamp) . ";" . $com->duration . ";" . $com->weight . ";" . $com->type . "\r\n";
            file_put_contents(ROOT_PATH . "/tempExportFiles/" . $filename, $line, FILE_APPEND);

        } //end of records loop

    } //end of telecommunications block

    if($telephones_number != 0) {

        $header = "\r\nnumber;type;country_code\r\n";
        file_put_contents(ROOT_PATH . "/tempExportFiles/" . $filename, $header, FILE_APPEND);

        //run the query
        try{
            $stmt = DB::get()->dbh->prepare($telephones_query);
            $stmt->execute();
        }catch(PDOException $e) {
            $result['result'] = "failure";
            $result['message'] = "Telephone numbers query failed! " . $e->getMessage();
            die(json_encode(array("reply" => $result)));
        }

        //records loop
        while($tel = $stmt->fetch()) {

            $line = $tel->number . ";" . $tel->type . ";" . $tel->country_code . "\r\n";
            file_put_contents(ROOT_PATH . "/tempExportFiles/" . $filename, $line, FILE_APPEND);

        } //end of records loop

    } //end of telecommunications block

    $result['link'] = "/tempExportFiles/" . $filename;

	die(json_encode(array("reply" => $result)));
	
}

function sanitize($string, $force_lowercase = true, $anal = false) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
        "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
        "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
    return ($force_lowercase) ?
        (function_exists('mb_strtolower')) ?
            mb_strtolower($clean, 'UTF-8') :
            strtolower($clean) :
        $clean;
}


?>