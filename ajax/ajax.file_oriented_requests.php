<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

function isFileInArray($array, $file) {
	
	for($i = 0 ; $i < count($array) ; $i++ ) {
		if($array[$i]['file_id'] == $file['file_id']) {
			return $i;
		}
	}
	
	return -1;	
}

if($_POST['uri'] == "getUploadedFiles") {
	
	$result = array("result" => "success",
					"data" => array());
	
	try {		
		
		$query = "SELECT 
					  " . Config::read('mysql.prefix') . "uploaded_files.generated_id, 
					  " . Config::read('mysql.prefix') . "uploaded_files.name
				  FROM 
					  " . Config::read('mysql.prefix') . "uploaded_files 				 
				  WHERE
					  " . Config::read('mysql.prefix') . "uploaded_files.uploader = :usr
				  ORDER BY
				  	  " . Config::read('mysql.prefix') . "uploaded_files.generated_id DESC";
		
		$usr = __S('user-username');

				
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":usr", $usr, PDO::PARAM_STR);
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Get uploaded files query failed: " . $e->getMessage())));
	}
	
	$files = array();
	
	while($f = $stmt->fetch()) {
		
		$file = array(  "file_id" => $f->generated_id,
						"file_name" => $f->name
						);
								
		
		
		$indexOf = isFileInArray($files, $file);
		
		if($indexOf == -1) { //then the file doesn't exist in array		
			
			//add the file to the files folder
			array_push($files, $file);
			
		} //end of if file exists already
				
	} //end of query itteration
	
	$result['data'] = $files;
		
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "renameFile") {
		
	$result = array("result" => "success",
					"message" => "",
					"name" => "");
	
	try {		
		
		$query = "UPDATE 
					  " . Config::read('mysql.prefix') . "uploaded_files 					  
				  SET 
					  " . Config::read('mysql.prefix') . "uploaded_files.name = :nam 
				  WHERE 
					  " . Config::read('mysql.prefix') . "uploaded_files.generated_id = :gid";				  
		
		$name = $_POST['name'];
		$id = $_POST['id'];
		
		if(!is_numeric($id)) {
			$result['result'] = "failure";
			$result['message'] = "Id can only be an integer!"; 
			die(json_encode(array("reply" => $result)));
		}
		
		$name = preg_replace('/[^[:alnum:][:space:][:dot]]/u', '', $name);
		
		if(strlen($name) > 255) {
			$result['result'] = "failure";
			$result['message'] = "Name can be up to 255 characters long!"; 
			die(json_encode(array("reply" => $result)));
		}		
				
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":gid", $id, PDO::PARAM_STR);
		$stmt->bindParam(":nam", $name, PDO::PARAM_STR);
		$stmt->execute();
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Rename uploaded file query failed: " . $e->getMessage())));
	}
		
	$result['message'] = "Your file has successfully been renamed!";
	$result['name'] = $name;
		
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "deleteFile") {
		
	$result = array("result" => "success",
					"message" => "",
					"name" => array());
					
	try {		
		
		$query = "SELECT 
					  file_path AS path
				  FROM
				  	  " . Config::read('mysql.prefix') . "uploaded_files 
				  WHERE 
					  " . Config::read('mysql.prefix') . "uploaded_files.generated_id = :gid";
		
		$id = $_POST['id'];
		
		if(!is_numeric($id)) {
			$result['result'] = "failure";
			$result['message'] = "Id can only be an integer!"; 
			die(json_encode(array("reply" => $result)));
		}	
				
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":gid", $id, PDO::PARAM_STR);
		$stmt->execute();
		
		$path = dirname(__FILE__) . '/../../master_uploaded_files/' . $stmt->fetch()->path;
		
		if(is_file($path)) {
			unlink($path);
		}
		
		$query = "DELETE 					  
				  FROM
				  	  " . Config::read('mysql.prefix') . "uploaded_files 
				  WHERE
					  " . Config::read('mysql.prefix') . "uploaded_files.generated_id = :gid";
					  
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":gid", $id, PDO::PARAM_STR);
		$stmt->execute();
		
		$result['message'] = "File was successfully deleted!";
		die(json_encode(array("reply" => $result)));
		
	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Delete uploaded file query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("error" => "Unexpected end of data!")));
	
}

if($_POST['uri'] == "importDataStart") {
	
	$result = array("result" => "success",
					"message" => "",
					"delimiter" => null,
					"available_templates" => null,
					);
	
	try {
			
		$query = "
			SELECT name
			FROM " . Config::read('mysql.prefix') . "import_templates		
		";
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->execute();
		
		$import_templates = array();
		
		while($f = $stmt->fetch()) {
			array_push($import_templates, $f->name);
		}
		
		$query = "
			SELECT setting_value
			FROM " . Config::read('mysql.prefix') . "settings
			WHERE setting_name = :snam
		";
		$snam = "csv_delimiter";
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":snam", $snam, PDO::PARAM_STR);
		$stmt->execute();
		
		$separator = $stmt->fetch();
		$separator = $separator->setting_value;
		
		$result['delimiter'] = $separator;
		$result['available_templates'] = $import_templates;
		$result['message'] = "Initiation completed";
		
		die(json_encode(array("reply" => $result)));
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
}

if($_POST['uri'] == "getFirstRows") {
		
	$result = array("result" => "success",
					"message" => "",
					"max_columns" => "",
					"available_filters" => "",
					"ignore_lines" => ""
					);
	
	$id = $_POST['id'];
	$template = ($_POST['template'] == "" || $_POST['template'] == null) ? null : $_POST['template'];
	
	$type = $_POST['type'];
	
	if(!is_numeric($id)) {
		$result['result'] = "faiure";
		$result['message'] = "The files id can only be an integer!";
		die(json_encode(array("reply" => $result)));
	}
	
	$filters = array();
	$fields = array();
		
	if($template != null) {
		try {
			//first get the filters
			$query = "SELECT 
						  * 
					  FROM
						  " . Config::read('mysql.prefix') . "file_filters
					  WHERE 
						  template_name = :tnam
					  ORDER BY
						  filter_order
					  ASC  
						  ";
							
			$stmt = DB::get()->dbh->prepare($query);
			$stmt->bindParam(":tnam", $template, PDO::PARAM_STR);
			$stmt->execute();
			
			while($f = $stmt->fetch()) {
				if($f->filter_type == "ignore_lines") { 
					$result['ignore_lines'] = $f->filter_value; 
					continue;
				}
				$filter = array(
					"type" => $f->filter_type,
					"value" => $f->filter_value,
					"column" => $f->column_index,
					"order" => $f->filter_order
				);
				
				array_push($filters, $filter);
			}
			
			//then get the assigned fields
			$query = "SELECT 
						  * 
					  FROM
						  " . Config::read('mysql.prefix') . "field_assignments
					  WHERE 
						  template_name = :tnam 
						  ";
							
			$stmt = DB::get()->dbh->prepare($query);
			$stmt->bindParam(":tnam", $template, PDO::PARAM_STR);
			$stmt->execute();
			
			while($f = $stmt->fetch()) {			
				$field = array(
					"column" => $f->column_index,
					"select_index" => $f->select_index,
					"optgroup" => $f->optgroup_index
				);
				
				array_push($fields, $field);
			}
		} catch(PDOException $e) {
			die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
		}
		
	}	
	
	try {
		//then get the file
		$query = "SELECT 
					  file_path AS path
				  FROM
					  " . Config::read('mysql.prefix') . "uploaded_files 
				  WHERE 
					  " . Config::read('mysql.prefix') . "uploaded_files.generated_id = :gid";
						
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":gid", $id, PDO::PARAM_STR);
		$stmt->execute();
		
		$f = $stmt->fetch();
		
		if($f == null || $f == false) {
			$result['result'] = "failure";
			$result['message'] = "Wrong file id!";
			die(json_encode(array("reply" => $result)));
		}
		
		ini_set('auto_detect_line_endings',TRUE);
		
		$path = dirname(__FILE__) . "/../../master_uploaded_files/" . $f->path;
		
		//process the files first 100 rows
		$row = 1;
		$rows = array();
		
		$max_columns = 0;
		
		if(strlen($_POST['delimiter']) > 1) { die(json_encode(array("error" => "Delimiter can only be 1 character long!"))); }
		
		if (($handle = fopen($path, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 0, $_POST['delimiter'])) !== FALSE) {
							
				if(count($data) > $max_columns) {
					$max_columns = count($data);
				}
							
				//if we re above 100 rows break the loop
				if($row == 101) {
					break;
				}
				
				if($data != null) {				
					array_push($rows, $data);
				}
				
				$row++;			
			}
			fclose($handle);
		}
		
		ini_set('auto_detect_line_endings',FALSE);
		
		//get availablefilter types select
		$filtersSelect = getAvailableFiltersSelect();
		
		//select for every available field
		$fieldsSelect = getAvailableFieldsSelect($type);
					
		$result['message'] = "";
		$result['data'] = $rows;
		$result['max_columns'] = $max_columns;
		$result['filters'] = $filters;
		$result['fields'] = $fields;
		$result['available_filters'] = $filtersSelect;
		$result['available_fields'] = $fieldsSelect;
		$result['saved_template'] = $_POST['template'];
		
		die(json_encode(array("reply" => $result)));
		
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
}

if($_POST['uri'] == "save_as_template") {
		
	$result = array("result" => "success",
					"message" => ""					
					);
	
	$name = $_POST['name'];
	$filters = $_POST['filters'];
	$fields = $_POST['fields'];
	$ignored_lines = $_POST['ignore'];
	
	try {
	
		//first delete the filter
		$query = "DELETE 				  
				  FROM
					  " . Config::read('mysql.prefix') . "import_templates
				  WHERE 
					  name = :tnam			    
					  ";					
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":tnam", $name, PDO::PARAM_STR);
		$stmt->execute();
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
	try {
		
		//then create it again
		$query = "INSERT 				  
				  INTO
					  " . Config::read('mysql.prefix') . "import_templates
				  (name) VALUES (:tnam)
					  ";
					  
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":tnam", $name, PDO::PARAM_STR);
		$stmt->execute();
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
	try {
		
		if(count($fields) > 0) {
			//then start associating fields
			$query = "INSERT INTO " . Config::read('mysql.prefix') . "field_assignments (column_index, select_index, optgroup_index, template_name) VALUES ";
			$counter = 0;
			foreach($fields as $field) {
				if($counter > 0) {
					$query .= ", ";
				}
				$query .= "(".$field['column'].", ".$field['select_index'].", ".$field['optgroup'].", '".$name."')";
				$counter++;
			}	
			$stmt = DB::get()->dbh->prepare($query);
			$stmt->execute();		
		}
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
		
	try {
		
		//then start associating filters		
		if(count($filters) > 0) {
			
			$query = "INSERT INTO " . Config::read('mysql.prefix') . "file_filters (filter_order, column_index, filter_type, filter_value, template_name) VALUES ";
			$counter = 0;
			foreach($filters as $filter) {
				if($counter > 0) {
					$query .= ", ";
				}
				$query .= "(".$filter['order'].", ".$filter['column'].", '".$filter['type']."', '".$filter['value']."', '".$name."')";
				$counter++;
			}	
			$stmt = DB::get()->dbh->prepare($query);
			$stmt->execute();
			
		}
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
	try {
	
		if($ignored_lines > 0) {
			$query = "INSERT INTO " . Config::read('mysql.prefix') . "file_filters (filter_order, column_index, filter_type, filter_value, template_name) 
																			VALUES (1, 1, 'ignore_lines', ".$ignored_lines.", '".$name."')";		
			$stmt = DB::get()->dbh->prepare($query);
			$stmt->execute();
		}
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "delete_template") {
		
	$result = array("result" => "success",
					"message" => ""					
					);
	
	$name = $_POST['template'];
	
	try {
	
		//first delete the filter
		$query = "DELETE 				  
				  FROM
					  " . Config::read('mysql.prefix') . "import_templates
				  WHERE 
					  name = :tnam			    
					  ";					
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":tnam", $name, PDO::PARAM_STR);
		$stmt->execute();
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
			
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "getSets") {
	
	$result = array("result" => "success",
					"message" => ""					
					);
	
	$username = __S("user-username");

	$level = User::isAdmin($username);

	try {
	
		//first delete the filter
		$query = "SELECT 
                    SETS.name AS name, COUNT(*) AS number, SETS.visibility AS visibility, SETS.creator_username AS creator
				  FROM
					  " . Config::read('mysql.prefix') . "datasets AS SETS
				  LEFT JOIN
				      ix_datasets_telecommunications AS COMS
				  ON
				      SETS.name = COMS.set_name
				  GROUP BY
				      SETS.name
					  ";					
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->bindParam(":una", $username, PDO::PARAM_STR);
		$stmt->execute();

		$sets = array();
		
		while($f = $stmt->fetch()) {
			
			if($level == "administrator" || $f->creator_username == $username || $f->visibility == 'public') {
				array_push($sets, array(
					"name" => $f->name,
					"visibility" => $f->visibility,
					"creator" => $f->creator,
                    "load" => $f->number
				));
			}
						
		}
		
		$result['message'] = $sets;
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
			
	die(json_encode(array("reply" => $result)));
	
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
	    if($e->getCode() == 23000) {
	        $result['result'] = "failure";
	        $result['message'] = "A dataset with that name already exists!";
	        die(json_encode(array("reply" => $result)));
        }
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
			
	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "ImportData") {

    $result = array("result" => "success",
        "message" => ""
    );

    $log = array("stats" => array());

    $D = $_POST['delimiter'];
    $COM_TYPE = $_POST['com_type'];
    $FILTERS = $_POST['filters'];
    $IGNORED_LINES = $_POST['ignored_lines'];
    $FIELDS = $_POST['fields'];
    $SET = $_POST['set'];
    $FILE_ID = $_POST['file_id'];

    $DATA = array();

    //first get the uploaded files path
    $file_query = "SELECT * FROM " . Config::read('mysql.prefix') . "uploaded_files WHERE generated_id = :gid";
    $stmt = DB::get()->dbh->prepare($file_query);
    $stmt->bindParam(":gid", $FILE_ID, PDO::PARAM_INT);
    $stmt->execute();

    //fetch the file record from the database
    $record = $stmt->fetch();

    //check if the file record was found in the database
    if ($record == false) {
        //return an error
        $result['result'] = "failure";
        $result['message'] = "Unable to find selected file!";
    }

    //check if the file was uploaded by another user
    if ($record->uploader != __S('user-username')) {
        //return an error
        $result['result'] = "failure";
        $result['message'] = "This file was uploaded by an other user. Unable to access it!";
    }

    //if no error was found we fetch the file path
    ini_set('auto_detect_line_endings', TRUE);
    $file_partial_path = $record->file_path;
    $file_path = dirname(__FILE__) . "/../../master_uploaded_files/" . $file_partial_path;

    //start and finish of the file
    $START = $_POST['startLine'];
    $LIMIT = $START + $_POST['limit'];

    //open the file
    if (($handle = fopen($file_path, "r")) !== FALSE) {

        $number_of_lines = 0;
        $errors = 0;
        $filtered_lines = 0;

        $stats = array(
            "lines" => 0,
            "errors" => 0,
            "filtered" => 0
        );

        $log["INSERTED_NUMBERS"] = 0;
        $log["DUBLICATE_NUMBERS"] = 0;
        $log["PROCESSED_NUMBERS"] = 0;

        $log["INSERTED_COMS"] = 0;
        $log["DUBLICATE_COMS"] = 0;
        $log["PROCESSED_COMS"] = 0;

        $log["INSERTED_PEOPLE"] = 0;
        $log["DUBLICATE_PEOPLE"] = 0;
        $log["PROCESSED_PEOPLE"] = 0;

        $log["INSERTED_ASSOCS"] = 0;
        $log["DUBLICATE_ASSOCS"] = 0;
        $log["PROCESSED_ASSOCS"] = 0;


        $result["INSERTED_NUMBERS"] = 0;
        $result["DUBLICATE_NUMBERS"] = 0;
        $result["PROCESSED_NUMBERS"] = 0;
        $result["INSERTED_COMS"] = 0;
        $result["DUBLICATE_COMS"] = 0;
        $result["PROCESSED_COMS"] = 0;

        $result["INSERTED_PEOPLE"] = 0;
        $result["DUBLICATE_PEOPLE"] = 0;
        $result["PROCESSED_PEOPLE"] = 0;

        $result["INSERTED_ASSOCS"] = 0;
        $result["DUBLICATE_ASSOCS"] = 0;
        $result["PROCESSED_ASSOCS"] = 0;

        $log['content'] = array();

        //set the final call var to false so there will be a next request
        $result['final_call'] = "false";

        //process the file line by line
        while (($lineParts = fgetcsv($handle, 0, $D)) !== FALSE) {

            if($number_of_lines < $START) {
                $number_of_lines++;
                continue;
            }

            if($number_of_lines > $LIMIT) {
                break;
            }

            //if the number of line is smaller than the ignore lines value then continue
            if ($START == 0 && $number_of_lines < $IGNORED_LINES) {
                $number_of_lines++;
                continue;
            }

            //here process the line
            if ($lineParts != null) {

                //if the $TYPE == telecommunication
                if ($COM_TYPE == "telecommunication") {

                    $caller = null;
                    $called = null;
                    $date = "";
                    $time = "";
                    $duration = null;
                    $type = "";
                    $weight = "";

                    if(isset($FILTERS) && !empty($FILTERS)) {
                        $lineParts = applyFilters($lineParts, $FILTERS);
                    }

                    //first look for the caller field and store its column index
                    for ($fieldsIndex = 0; $fieldsIndex < count($FIELDS); $fieldsIndex++) {

                        //first check that there isn't an excluded filter active for this field
                        if ($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)] == "-excluded-") {
                            //if the field is excluded then continue to the next line
                            array_push($log['content'], array("category" => "filtered", "reason" => "Excluded filter", "value" => "", "line" => $number_of_lines+1));
                            $stats['filtered']++;
                            continue 2;
                        }

                        $field = getField($FIELDS[$fieldsIndex]['optgroup'], $FIELDS[$fieldsIndex]['select_index']);

                        //caller case
                        if ($field['entity'] == "caller" && $field['field'] == "number") {
                            //creating this lines caller object
                            $caller = new Telephone($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)], "unknown");
                            continue;
                        } // end of caller case

                        //called case
                        if ($field['entity'] == "called" && $field['field'] == "number") {
                            //creating this lines called object
                            $called = new Telephone($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)], "unknown");
                        } // end of called case

                    } // end of the loop that searches for the caller and called entities in the FIELDS array

                    //reiterate through the remaining fields array to find the rest of the fields
                    for ($fieldsIndex = 0; $fieldsIndex < count($FIELDS); $fieldsIndex++) {

                        if ($fieldsIndex >= count($FIELDS)) {
                            //error here and continue
                            continue;
                        }

                        $field = getField($FIELDS[$fieldsIndex]['optgroup'], $FIELDS[$fieldsIndex]['select_index']);

                        //type case
                        if ($field['entity'] == "caller" && $field['field'] == "type") {
                            $caller->setType($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //type case
                        if ($field['entity'] == "called" && $field['field'] == "type") {
                            $called->setType($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //country code case
                        if ($field['entity'] == "caller" && $field['field'] == "country_code") {
                            $caller->setCountryCode($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //country code case
                        if ($field['entity'] == "called" && $field['field'] == "country_code") {
                            $called->setCountryCode($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //date case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "date") {
                            $date = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //time case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "time") {
                            $time = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //timestamp case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "timestamp") {
                            $timestamp = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            $tsParts = explode(" ", $timestamp);
                            $date = $tsParts[0];
                            $time = $tsParts[1];
                            continue;
                        }

                        //duration (seconds) case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "duration_seconds") {
                            $duration = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //duration (time) case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "duration_time") {
                            $duration = timeToInt($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //type case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "type") {
                            $type = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //weight case
                        if ($field['entity'] == "telecommunication" && $field['field'] == "weight") {
                            $weight = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                        }

                    } // end of the loop that searches for the rest of the fields

                    //by now i should have all the mandatory fields for a telecommunication
                    if (!$caller->isValid()) {
                        //log this error and continue
                        array_push($log['content'], array("category" => "error", "reason" => "Invalid caller!", "value" => $caller->getValidity(), "line" => $number_of_lines+1));
                        $errors++;
                        continue;
                    }

                    if (!$called->isValid()) {
                        //log this error and continue
                        array_push($log['content'], array("category" => "error", "reason" => "Invalid called!", "value" => $called->getValidity(), "line" => $number_of_lines+1));
                        $errors++;
                        continue;
                    }

                    $com = new Telecommunication($caller, $called, $date, $time, $duration);

                    $com->setType($type);
                    $com->setWeight($weight);

                    //check the validity of the communication
                    if (!$com->isValid()) {
                        //log the error and continue
                        array_push($log['content'], array("category" => "error", "reason" => "Invalid telecommunication!", "value" => $com->getValidity(), "line" => $number_of_lines+1));
                        $errors++;
                        continue;
                    }

                    //add the communication in the coms array
                    array_push($DATA, $com);

                    //log this as a successful case
                    array_push($log['content'], array("category" => "success", "reason" => "", "value" => "", "line" => $number_of_lines+1));

                }//end of the if telecommunication block

                //if the $TYPE == people
                if ($COM_TYPE == "people") {

                    $idNum = "";
                    $surname = "";
                    $name = "";
                    $gender = "";
                    $alias = "";
                    $address = "";
                    $fathername = "";
                    $mothername = "";
                    $ssn = "";
                    $birthdate = "";
                    $country = "";

                    $number = "";
                    $type = "";
                    $country_code = "";

                    for ($fieldsIndex = 0; $fieldsIndex < count($FIELDS); $fieldsIndex++) {

                        //first check that there isn't an excluded filter active for this field
                        if ($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)] == "-excluded-") {
                            //if the field is excluded then continue to the next line
                            array_push($log['content'], array("category" => "filtered", "reason" => "Excluded filter", "value" => "", "line" => $number_of_lines+1));
                            $stats['filtered']++;
                            continue 2;
                        }

                        $field = getPeopleField($FIELDS[$fieldsIndex]['optgroup'], $FIELDS[$fieldsIndex]['select_index']);

                        //number case
                        if ($field['entity'] == "telephone" && $field['field'] == "number") {
                            //creating this lines caller object
                            $number = new Telephone($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)], "unknown");
                            continue;
                        } // end of telephone number case

                    } // end of the loop that searches for the caller and called entities in the FIELDS array

                    //reiterate through the remaining fields array to find the rest of the fields
                    for ($fieldsIndex = 0; $fieldsIndex < count($FIELDS); $fieldsIndex++) {

                        if ($fieldsIndex >= count($FIELDS)) {
                            //error here and continue
                            continue;
                        }

                        $field = getPeopleField($FIELDS[$fieldsIndex]['optgroup'], $FIELDS[$fieldsIndex]['select_index']);

                        //type case
                        if ($field['entity'] == "telephone" && $field['field'] == "type") {
                            $number->setType($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //country code case
                        if ($field['entity'] == "telephone" && $field['field'] == "country_code") {
                            $number->setCountryCode($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //id number case
                        if ($field['entity'] == "person" && $field['field'] == "id_number") {
                            $idNum = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //surname case
                        if ($field['entity'] == "person" && $field['field'] == "surname") {
                            $surname = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //name case
                        if ($field['entity'] == "person" && $field['field'] == "name") {
                            $name = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //alias case
                        if ($field['entity'] == "person" && $field['field'] == "alias") {
                            $alias = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //gender case
                        if ($field['entity'] == "person" && $field['field'] == "gender") {
                            $gender = timeToInt($lineParts[($FIELDS[$fieldsIndex]['column'] - 1)]);
                            continue;
                        }

                        //address case
                        if ($field['entity'] == "person" && $field['field'] == "address") {
                            $address = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //fathersnmae case
                        if ($field['entity'] == "person" && $field['field'] == "fathersname") {
                            $fathername = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //mothersname  case
                        if ($field['entity'] == "person" && $field['field'] == "mothersname") {
                            $mothername = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //ssn  case
                        if ($field['entity'] == "person" && $field['field'] == "ssn") {
                            $ssn = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //country  case
                        if ($field['entity'] == "person" && $field['field'] == "country") {
                            $country = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                        //birthdate  case
                        if ($field['entity'] == "person" && $field['field'] == "birthdate") {
                            $birthdate = $lineParts[($FIELDS[$fieldsIndex]['column'] - 1)];
                            continue;
                        }

                    } // end of the loop that searches for the rest of the fields

                    //by now i should have all the mandatory fields for a telephone number
                    if (!$number->isValid()) {
                        //log this error and continue
                        array_push($log['content'], array("category" => "error", "reason" => "Invalid telephone number!", "value" => $number->getValidity(), "line" => $number_of_lines+1));
                        $errors++;
                        continue;
                    }

                    $person = new Person();

                    $person->setSSN($ssn);
                    $person->setAddress($address);
                    $person->setAlias($alias);
                    $person->setBirthdate($birthdate);
                    $person->setCountry($country);
                    $person->setFathername($fathername);
                    $person->setMothername($mothername);
                    $person->setGender($gender);
                    $person->setSurname($surname);
                    $person->setName($name);
                    $person->setIdNum($idNum);

                    if(!$person->isValid()) {
                        array_push($log['content'], array("category" => "error", "reason" => $person->getValidity(), "value" => "", "line" => $number_of_lines+1));
                        continue;
                    }

                    //add the communication in the coms array
                    array_push($DATA, array("telephone" => $number, "person" => $person));

                    //log this as a successful case
                    array_push($log['content'], array("category" => "success", "reason" => "", "value" => "", "line" => $number_of_lines+1));

                }//end of the if people block

            } // end of the if line isn't null block

            $number_of_lines++;

        } //end of the lines loop

        if($number_of_lines <= $LIMIT) {
            $result['final_call'] = "true";
        }

        //start of the inserter part
        $replyStats = insertDataPart($DATA, $COM_TYPE, $SET);

        //increment the log entries for this part of the data
        $log["INSERTED_NUMBERS"] += $replyStats["INSERTED_NUMBERS"];
        $log["DUBLICATE_NUMBERS"] += $replyStats['DUBLICATE_NUMBERS'];
        $log["PROCESSED_NUMBERS"] += $replyStats['PROCESSED_NUMBERS'];

        $log["INSERTED_COMS"] += $replyStats['INSERTED_COMS'];
        $log["DUBLICATE_COMS"] += $replyStats['DUBLICATE_COMS'];
        $log["PROCESSED_COMS"] += $replyStats['PROCESSED_COMS'];

        $log["INSERTED_PEOPLE"] += $replyStats['INSERTED_PEOPLE'];
        $log["DUBLICATE_PEOPLE"] += $replyStats['DUBLICATE_PEOPLE'];
        $log["PROCESSED_PEOPLE"] += $replyStats['PROCESSED_PEOPLE'];

        $log["INSERTED_ASSOCS"] += $replyStats['INSERTED_ASSOCS'];
        $log["DUBLICATE_ASSOCS"] += $replyStats['DUBLICATE_ASSOCS'];
        $log["PROCESSED_ASSOCS"] += $replyStats['PROCESSED_ASSOCS'];


        $result["INSERTED_NUMBERS"] += $replyStats["INSERTED_NUMBERS"];
        $result["DUBLICATE_NUMBERS"] += $replyStats["DUBLICATE_NUMBERS"];
        $result["PROCESSED_NUMBERS"] += $replyStats["PROCESSED_NUMBERS"];

        $result["INSERTED_COMS"] += $replyStats["INSERTED_COMS"];
        $result["DUBLICATE_COMS"] += $replyStats["DUBLICATE_COMS"];
        $result["PROCESSED_COMS"] += $replyStats["PROCESSED_COMS"];

        $result["INSERTED_PEOPLE"] += $replyStats["INSERTED_PEOPLE"];
        $result["DUBLICATE_PEOPLE"] += $replyStats["DUBLICATE_PEOPLE"];
        $result["PROCESSED_PEOPLE"] += $replyStats["PROCESSED_PEOPLE"];

        $result["INSERTED_ASSOCS"] += $replyStats["INSERTED_ASSOCS"];
        $result["DUBLICATE_ASSOCS"] += $replyStats["DUBLICATE_ASSOCS"];
        $result["PROCESSED_ASSOCS"] += $replyStats["PROCESSED_ASSOCS"];
        //end of the inserter part

        fclose($handle);

        $stats['lines'] = $number_of_lines;
        $stats['errors'] = $errors;

        if($START == 0) {
            $stats['filtered'] += $IGNORED_LINES;
        }

        $log['stats'] = $stats;

    } else { //error trying to open the file
        $result['result'] = "failure";
        $result['message'] = "Could not open this file!";
    }

    ini_set('auto_detect_line_endings', FALSE);

    //create a log here
    if(!isset($_POST['log_path']) || empty($_POST['log_path'])) {
        //create a new log here and write the first data
        $logObj = new log($log, intToDateAndTime(Now()), null);
        $result['log_path'] = $logObj->getPath();
    } else {
        //create an increment log here and write this parts data
        $logObj = new log($log, intToDateAndTime(Now()), $_POST['log_path']);
        $result['log_path'] = $_POST['log_path'];
    }

    $result['lines'] = $log['stats']['lines'];
    $result['errors'] = $log['stats']['errors'];
    $result['filtered'] = $log['stats']['filtered'];

    $result['next_start'] = $LIMIT + 1;

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "deleteImportTemplate") {

    $result = array("result" => "success",
        "message" => ""
    );

    $template = $_POST['id'];

    try {

        //first delete the filter
        $query = "DELETE FROM
					  " . Config::read('mysql.prefix') . "import_templates
				  WHERE
				      name = :nam
					  ";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":nam", $template, PDO::PARAM_STR);
        $stmt->execute();

        $result['message'] = "Import template has been deleted successfully.";

    } catch(PDOException $e) {
        die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
    }

    die(json_encode(array("reply" => $result)));

}

function getPeopleField($optgroup, $index) {

    if($optgroup == 1) {
        if($index == 0) {
            return array("entity" => "person", "field" => "id_number");
        }
        if($index == 1) {
            return array("entity" => "person", "field" => "ssn");
        }
        if($index == 2) {
            return array("entity" => "person", "field" => "surname");
        }
        if($index == 3) {
            return array("entity" => "person", "field" => "name");
        }
        if($index == 4) {
            return array("entity" => "person", "field" => "birthdate");
        }
        if($index == 5) {
            return array("entity" => "person", "field" => "gender");
        }
        if($index == 6) {
            return array("entity" => "person", "field" => "fathersname");
        }
        if($index == 7) {
            return array("entity" => "person", "field" => "mothersname");
        }
        if($index == 8) {
            return array("entity" => "person", "field" => "alias");
        }
        if($index == 9) {
            return array("entity" => "person", "field" => "address");
        }
        if($index == 10) {
            return array("entity" => "person", "field" => "country");
        }
    }

    if($optgroup == 2) {
        if($index == 0) {
            return array("entity" => "telephone", "field" => "number");
        }
        if($index == 1) {
            return array("entity" => "telephone", "field" => "type");
        }
        if($index == 2) {
            return array("entity" => "telephone", "field" => "country_code");
        }
    }

}

function getField($optgroup, $index) {
	
	if($optgroup == 1) {
		if($index == 0) {
			return array("entity" => "caller", "field" => "number");
		}
		//if($index == 1) {
		//	return array("entity" => "caller", "field" => "imei");
		//}
		if($index == 1) {
			return array("entity" => "caller", "field" => "type");
		}
		if($index == 2) {
			return array("entity" => "caller", "field" => "country_code");
		}
	}
	
	if($optgroup == 2) {
		if($index == 0) {
			return array("entity" => "called", "field" => "number");
		}
	//	if($index == 1) {
	//		return array("entity" => "called", "field" => "imei");
	//	}
		if($index == 1) {
			return array("entity" => "called", "field" => "type");
		}
		if($index == 2) {
			return array("entity" => "called", "field" => "country_code");
		}
	}
	
	if($optgroup == 3) {
		if($index == 0) {
			return array("entity" => "telecommunication", "field" => "timestamp");
		}
		if($index == 1) {
			return array("entity" => "telecommunication", "field" => "date");
		}
		if($index == 2) {
			return array("entity" => "telecommunication", "field" => "time");
		}
		if($index == 3) {
			return array("entity" => "telecommunication", "field" => "duration_seconds");
		}
		if($index == 4) {
			return array("entity" => "telecommunication", "field" => "duration_time");
		}
		if($index == 5) {
			return array("entity" => "telecommunication", "field" => "type");
		}
		if($index == 6) {
			return array("entity" => "telecommunication", "field" => "weight");
		}
	}
	
	return null;
	
}

function getAvailableFieldsSelect($type) {
	
	if($type == "people") {
	
		$fieldsSelect = "<select class='available-fields'>";
		
			$fieldsSelect .= "<option rel='choose'>Assign Field...</option>";

			$fieldsSelect .= "<optgroup label='Person'>";
			
				$fieldsSelect .= "<option rel='id_number'>ID Number</option>";
				$fieldsSelect .= "<option rel='ssn'>Social Security Number</option>";
				$fieldsSelect .= "<option rel='surname'>Surname</option>";
				$fieldsSelect .= "<option rel='name'>Name</option>";
				$fieldsSelect .= "<option rel='birthdate'>Birthdate</option>";
				$fieldsSelect .= "<option rel='gender'>Gender M/F</option>";
				$fieldsSelect .= "<option rel='fathername'>Fathers Name</option>";
				$fieldsSelect .= "<option rel='mothername'>Mothers Name</option>";
				$fieldsSelect .= "<option rel='alias'>Alias/Nickname</option>";
				$fieldsSelect .= "<option rel='address'>Address</option>";
				$fieldsSelect .= "<option rel='country'>Home Country</option>";

			$fieldsSelect .= "</optgroup>";

			$fieldsSelect .= "<optgroup label='Number'>";

                $fieldsSelect .= "<option rel='number'>Telephone Number</option>";
                $fieldsSelect .= "<option rel='type'>Type (landline/cellphone)</option>";
                $fieldsSelect .= "<option rel='country_code'>Country Code (ex. 0030)</option>";

			$fieldsSelect .= "</optgroup>";
			
		$fieldsSelect .= "</select>";
		
		return $fieldsSelect;
	}
	
	if($type == "telecommunication") {
		
		$fieldsSelect = "<select class='available-fields'>";
		
			$fieldsSelect .= "<option rel='choose'>Assign Field...</option>";
			
			$fieldsSelect .= "<optgroup label='Telephone 1'>";
					
				$fieldsSelect .= "<option telephone='1' rel='number'>Telephone Number 1</option>";
				//$fieldsSelect .= "<option telephone='1' rel='imei'>Imei Number</option>";
				$fieldsSelect .= "<option telephone='1' rel='type'>Type (landline/cellphone)</option>";
				$fieldsSelect .= "<option telephone='1' rel='country_code'>Country Code (ex. 0030)</option>";
			
			$fieldsSelect .= "</optgroup>";
			
			$fieldsSelect .= "<optgroup label='Telephone 2'>";
			
				$fieldsSelect .= "<option telephone='2' rel='number'>Telephone Number 2</option>";
				//$fieldsSelect .= "<option telephone='2' rel='imei'>Imei Number</option>";
				$fieldsSelect .= "<option telephone='2' rel='type'>Type (landline/cellphone)</option>";
				$fieldsSelect .= "<option telephone='2' rel='country_code'>Country Code (ex. 0030)</option>";
			
			$fieldsSelect .= "</optgroup>";
			
			$fieldsSelect .= "<optgroup label='Telecommunication'>";
				
				$fieldsSelect .= "<option rel='timestamp'>Timestamp</option>";
				$fieldsSelect .= "<option rel='date'>Date</option>";
				$fieldsSelect .= "<option rel='time'>Time</option>";				
				$fieldsSelect .= "<option rel='duration'>Duration (seconds)</option>";
				$fieldsSelect .= "<option rel='duration'>Duration (time format)</option>";
				$fieldsSelect .= "<option rel='time'>Type</option>";
				$fieldsSelect .= "<option rel='weight'>Weight</option>";
			
			$fieldsSelect .= "</optgroup>";
		
		$fieldsSelect .= "</select>";
		
		return $fieldsSelect;
		
	}
	
}

function getAvailableFiltersSelect() {
	
	try {
	
		//select for every available filter type
		$query = "
			SELECT SUBSTRING(COLUMN_TYPE,5) as types
			FROM information_schema.COLUMNS
			WHERE TABLE_SCHEMA='addom_masterdb' 
			AND TABLE_NAME='" . Config::read('mysql.prefix') . "file_filters'
			AND COLUMN_NAME='filter_type'
		";
		$stmt = DB::get()->dbh->prepare($query);
		$stmt->execute();
			
		$filter_types = $stmt->fetch();
		
		$filterString = $filter_types->types;
		
		$filterString = str_replace("(","", $filterString);
		$filterString = str_replace(")","", $filterString);
		$filterString = str_replace("'","", $filterString);
		
		$typesArray = explode(",", $filterString);
		
		$select = "<select class='available-filters'>";
		
		$select .= "<option rel='choose'>Apply Filter...</option>";
		
		foreach($typesArray as $type) {
			
			if($type=="ignore_lines") {continue;}
			
			$rel = $type;
			$label = ucwords(str_replace("_", " ", $type));		
			
			$select .= "<option rel='".$rel."'>".$label."</option>";
		}
		
		$select .= "</select>";
		
		return $select;
	
	} catch(PDOException $e) {
		die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
	}
	
}

function applyFilters($csvRowArray, $filters) {

	if(count($filters) > 0) {
		
		$newRow = $csvRowArray;
		
		//for every column of the line
		for($i = 0 ; $i < count($newRow); $i++) {
			
			//for every filter
			for($j = 0 ; $j < count($filters); $j++) {
								
				if($i == $filters[$j]['column']-1) {
					
					//type of filter remove prefix
					if($filters[$j]['type'] == 'remove_prefix') {						
						if(startsWith($newRow[$i], $filters[$j]['value'])) {
							$newRow[$i] = substr($newRow[$i], strlen($filters[$j]['value']), strlen($newRow[$i]));							
						}						
					}
					
					//another type... remove_suffix
					if($filters[$j]['type'] == 'remove_suffix') {
						if(endsWith($newRow[$i], $filters[$j]['value'])) {
							$newRow[$i] = substr($newRow[$i], 0, (strlen($newRow[$i]) - strlen($filters[$j]['value'])));
						}						
					}
					
					//another type... 'add_prefix'
					if($filters[$j]['type'] == 'add_prefix') {						
						$newRow[$i] = $filters[$j]['value'] . $newRow[$i];											
					}
					
					//another type... 'add_suffix'
					if($filters[$j]['type'] == 'add_suffix') {
						$newRow[$i] = $newRow[$i] . $filters[$j]['value'];
					}
					
					//another type... remove_fixed_prefix
					if($filters[$j]['type'] == 'remove_fixed_prefix') {						
						$newRow[$i] = substr($newRow[$i], $filters[$j]['value'], strlen($newRow[$i]));
					}
					
					//another type...remove_fixed_suffix
					if($filters[$j]['type'] == 'remove_fixed_suffix') {						
						$newRow[$i] = substr($newRow[$i], 0, (strlen($newRow[$i]) - $filters[$j]['value']));
					}
					
					//another type...ignore_lines'
					if($filters[$j]['type'] == 'exclude_if_contains') {						
						if (strpos($newRow[$i], $filters[$j]['value']) !== false) {
							$newRow[$i] = "-excluded-";
						}
					}
					
					//another type...ignore_lines'
					if($filters[$j]['type'] == 'replace_with_null') {						
						$newRow[$i] = str_replace($filters[$j]['value'], "", $newRow[$i]);
					}
										
				}				
			} //end of filters loop
			
		} //end of columns loop
		
		return $newRow;
		
	}

}

function insertDataPart($DATA, $COM_TYPE, $SET) {

    $REPLY_ARRAY = array("log" => array());

    //and insert the data
    $insertType = "";

    if ($COM_TYPE == 'telecommunication') {
        $insertType = DatabaseInserter::$TELECOMMUNICATIONS;
    }

    if ($COM_TYPE == 'people') {
        $insertType = DatabaseInserter::$PEOPLE;
    }

    $inserter = new DatabaseInserter($DATA, $SET, $insertType);

    try {

        $stats = $inserter->insertData();

        $REPLY_ARRAY["INSERTED_NUMBERS"] = $stats['INSERTED_NUMBERS'];
        $REPLY_ARRAY["DUBLICATE_NUMBERS"] = $stats['DUBLICATE_NUMBERS'];
        $REPLY_ARRAY["PROCESSED_NUMBERS"] = $stats['PROCESSED_NUMBERS'];

        $REPLY_ARRAY["INSERTED_COMS"] = $stats['INSERTED_COMS'];
        $REPLY_ARRAY["DUBLICATE_COMS"] = $stats['DUBLICATE_COMS'];
        $REPLY_ARRAY["PROCESSED_COMS"] = $stats['PROCESSED_COMS'];

        $REPLY_ARRAY["INSERTED_PEOPLE"] = $stats['INSERTED_PEOPLE'];
        $REPLY_ARRAY["DUBLICATE_PEOPLE"] = $stats['DUBLICATE_PEOPLE'];
        $REPLY_ARRAY["PROCESSED_PEOPLE"] = $stats['PROCESSED_PEOPLE'];

        $REPLY_ARRAY["INSERTED_ASSOCS"] = $stats['INSERTED_ASSOCS'];
        $REPLY_ARRAY["DUBLICATE_ASSOCS"] = $stats['DUBLICATE_ASSOCS'];
        $REPLY_ARRAY["PROCESSED_ASSOCS"] = $stats['PROCESSED_ASSOCS'];

        $REPLY_ARRAY["INSERTED_NUMBERS"] = $stats['INSERTED_NUMBERS'];
        $REPLY_ARRAY["DUBLICATE_NUMBERS"] = $stats['DUBLICATE_NUMBERS'];
        $REPLY_ARRAY["PROCESSED_NUMBERS"] = $stats['PROCESSED_NUMBERS'];
        $REPLY_ARRAY["INSERTED_COMS"] = $stats['INSERTED_COMS'];
        $REPLY_ARRAY["DUBLICATE_COMS"] = $stats['DUBLICATE_COMS'];
        $REPLY_ARRAY["PROCESSED_COMS"] = $stats['PROCESSED_COMS'];

        $REPLY_ARRAY["INSERTED_PEOPLE"] = $stats['INSERTED_PEOPLE'];
        $REPLY_ARRAY["DUBLICATE_PEOPLE"] = $stats['DUBLICATE_PEOPLE'];
        $REPLY_ARRAY["PROCESSED_PEOPLE"] = $stats['PROCESSED_PEOPLE'];

        $REPLY_ARRAY["INSERTED_ASSOCS"] = $stats['INSERTED_ASSOCS'];
        $REPLY_ARRAY["DUBLICATE_ASSOCS"] = $stats['DUBLICATE_ASSOCS'];
        $REPLY_ARRAY["PROCESSED_ASSOCS"] = $stats['PROCESSED_ASSOCS'];

        return $REPLY_ARRAY;

    } catch(PDOException $e) {
        die(json_encode(array("error" => "InsertData raised an exception: " . $e->getMessage())));
    }

}

?>