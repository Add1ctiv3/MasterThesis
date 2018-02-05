<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");
include_once(dirname(__FILE__) . "/../classes/class.scanner.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['username'] != __S("user-username")) { echo json_encode(array("result" => "error", "message" => "You can only upload files logged in with your username!")); exit; }

//make the folder
if(!file_exists("../../master_uploaded_files/" . strtolower($_POST['username'])) || !is_dir("../../master_uploaded_files/" . strtolower($_POST['username']))) {
	mkdir("../../master_uploaded_files/" . strtolower($_POST['username']));
}

$now = Now();
$nowStr = intToDateAndTime($now);
$nowSql = date("Y-m-d H:i:s", $now);

$folder = '../../master_uploaded_files/' . strtolower($_POST['username']) . '/';
$title = __S('user-username') . "_" . $now;

$fullPath = $folder . $title;
$fileName = $nowStr . '.csv';

$sqlPath = "/" . strtolower($_POST['username']) . "/" . $title . ".csv";

// initialize FileUploader
$FileUploader = new FileUploader('files', array(
	'uploadDir' => $folder,
	'title' => $title
));

// call to upload the files
$data = $FileUploader->upload();

// if warnings
if($data['hasWarnings']) {
	// get warnings
	$warnings = $data['warnings'];
	echo json_encode(array("result" => "warning", "warnings" => $warnings));
	exit;
}

// unlink the files
// !important only for appended files
// you will need to give the array with appendend files in 'files' option of the FileUploader
foreach($FileUploader->getRemovedFiles('file') as $key=>$value) {
	unlink('../uploads/' . $value['name']);
}

// get the fileList
//$fileList = $FileUploader->getFileList();
//print_r($fileList);

// success
if($data['isSuccess'] && count($data['files']) > 0) {
	// get uploaded files
	//$uploadedFiles = $data['files'];
	
	//first scan the document
	$scanner = new Scanner($fullpath);
	$reply = $scanner->scan();
	if($reply['result'] == "infected") {
		echo json_encode(array("result" => "error", "message" => "The file appears to contain malicious content!"));
		exit;
	}
	
	//query	
	try {
		$stmt = DB::get()->dbh->prepare("INSERT INTO " . Config::read('mysql.prefix') . "uploaded_files (file_path, uploader, name, date) VALUES (:v1, :v2, :v3, :v4)");
		$stmt->bindParam(":v1", $sqlPath, PDO::PARAM_STR);
		$stmt->bindParam(":v2", $_POST['username'], PDO::PARAM_STR);
		$stmt->bindParam(":v3", $fileName, PDO::PARAM_STR);
		$stmt->bindParam(":v4", $nowSql, PDO::PARAM_STR);
		$stmt->execute();
	}
	catch(PDOException $e) {
		echo json_encode(array("result" => "error", "message" => $e->getMessage()));
		exit;
	}
	
	echo json_encode(array("result" => "success", "message" => "Your file has been uploaded!"));
	exit;
}

echo json_encode(array("result" => "error", "message" => "Something went wrong!"));

?>