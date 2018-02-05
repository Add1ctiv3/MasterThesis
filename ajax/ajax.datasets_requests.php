<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['uri'] == "deleteDatasets") {
	
	$result = array("result" => "success",
					"message" => "");

	$ids = $_POST['ids'];

    $queryWHERE = "";

	foreach($ids as $id) {

	    $queryWHERE .= " name = '".$id."' OR ";

    }

    $queryWHERE = substr($queryWHERE, 0, strlen($queryWHERE)-3) . ";";
	
	try {		
		
		$query = "DELETE FROM 
					  " . Config::read('mysql.prefix') . "datasets 				 
				  WHERE 
					  " . $queryWHERE;

		$stmt = DB::get()->dbh->prepare($query);
		$qresult = $stmt->execute();

		if($qresult) {
		    $result['message'] = "The selected datasets were successfully deleted!";
        } else {
		    $result['result'] = "failure";
		    $result['message'] = "Delete datasets query failed!";
        }

	}
	catch(PDOException $e) {
		die(json_encode(array("error" => "Delete datasets query failed: " . $e->getMessage())));
	}

	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "newSet") {

    $result = array("result" => "success",
        "message" => "",
        "info_message" => ""
    );

    $username = __S("user-username");
    $name = $_POST['name'];
    $visibility= $_POST['type'];

    try {
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
            $result["message"] = "A dataset with this name already exists!";
            die(json_encode(array("reply" => $result)));
        }
        die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
    }

    try {

        $query = "SELECT creation_timestamp, creator_username FROM " . Config::read('mysql.prefix') . "datasets WHERE name = :namm";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->bindParam(":namm", $name, PDO::PARAM_STR);
        $stmt->execute();

        $set = $stmt->fetch();

        $result['info_message'] = $set->creation_timestamp . " created by " . $set->creator_username;

    } catch(PDOException $e) {
        die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
    }

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "getAvailableDatasetsForCombining") {

    $result = array("result" => "success",
        "message" => "",
        "data" => ""
    );

    $sets = array();

    try {
        $query = "SELECT name FROM  " . Config::read('mysql.prefix') . "datasets";
        $stmt = DB::get()->dbh->prepare($query);
        $stmt->execute();

        $user = User::getLoggedUser();

        while($rec = $stmt->fetch()) {
            if($user['type'] == "administrator" || $user['access-level'] == 5 || $rec->creator_username == $user['username']) {
                //exclude the dataset if its already selected
                if(isset($_POST['alreadySelected']) && !empty($_POST['alreadySelected']) && count($_POST['alreadySelected']) > 0 && in_array($rec->name, $_POST['alreadySelected'])) {
                    continue;
                }
                array_push($sets, $rec->name);
            }
        }

    } catch(PDOException $e) {
        die(json_encode(array("error" => "Query failed: " . $e->getMessage())));
    }

    $result['message'] = "";
    $result['data'] = $sets;

    die(json_encode(array("reply" => $result)));

}

if($_POST["uri"] == "combineDatasets") {

    $result = array("result" => "success",
        "message" => "",
        "data" => array("name" => "", "infoLine" => "")
    );

    $mode = $_POST["mode"];
    $sets = $_POST["datasets"];
    $newSet = $_POST["newName"];

    if(!isset($newSet) || empty($newSet)) {
        $result['message'] = "You need to provide a set name for the set in which the resulting records will be saved.";
        $result['result'] = "failure";
        die(json_encode(array("reply" => $result)));
    }

    //check if there is a set name or we need to create it
    $user = User::getLoggedUser();

    $setCreationQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets (name, creator_username, visibility) VALUES ('".$newSet."', '".$user['username']."', 'public')";

    try {
        $stmt = DB::get()->dbh->prepare($setCreationQuery);
        $stmt->execute();
    } catch(PDOException $e) {
        die(json_encode(array("error" => "Query set creation failed: " . $e->getMessage())));
    }

    if($mode == "union") {

        //first deal with the telecommunications
        $query = "SELECT * FROM " . Config::read('mysql.prefix') . "datasets_telecommunications WHERE ";

        //finish building the select query
        foreach($sets as $set) {
            $query .= " set_name = '" . $set . "' OR ";
        }

        $query = substr($query, 0, strlen($query) - 3);

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();

            $pdo = DB::get()->dbh;

            $pdo->beginTransaction();

            while($rec = $stmt->fetch()) {
                $comSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telecommunications (set_name, caller, called, time_stamp, duration) VALUES 
                                    ('".$newSet."', '".$rec->caller."', '".$rec->called."', ".$rec->time_stamp.", ".$rec->duration.");";
                try {
                    $stmt2 = $pdo->prepare($comSetQuery);
                    $stmt2->execute();
                }catch(PDOException $e) {
                    //nothing
                }
            }
            $pdo->commit();

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Union query 1 failed: " . $e->getMessage())));
        }

        //then deal with the numbers
        $query = "SELECT * FROM " . Config::read('mysql.prefix') . "datasets_telephone_numbers WHERE ";

        //finish building the select query
        foreach($sets as $set) {
            $query .= " set_name = '" . $set . "' OR";
        }

        $query = substr($query, 0, strlen($query) - 3);

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();

            $pdo = DB::get()->dbh;

            $pdo->beginTransaction();

            while($rec = $stmt->fetch()) {
                $comSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) VALUES 
                                    ('".$newSet."', '".$rec->number."');";
                try {
                    $stmt2 = $pdo->prepare($comSetQuery);
                    $stmt2->execute();
                }catch(PDOException $e) {
                    //nothing
                }
            }
            $pdo->commit();

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Union query 2 failed: " . $e->getMessage())));
        }

    } //end of union mode

    if($mode == "intersection") {

        $setA = $sets[0];
        $setB = $sets[1];

        $intersectionSelectQuery = "  SELECT
                        caller, called, time_stamp, duration
                    FROM
                        " . Config::read('mysql.prefix') . "datasets_telecommunications
                    WHERE
                        set_name = '".$setA."' OR set_name = '".$setB."'
                    GROUP BY
                        caller, called, time_stamp, duration
                    HAVING
                        COUNT(*) = 2";

        $telephonesIntersectionQuery = "SELECT
                                            number
                                        FROM
                                            " . Config::read('mysql.prefix') . "datasets_telephone_numbers
                                        WHERE
                                            set_name = '".$setA."' OR set_name = '".$setB."'
                                        GROUP BY
                                            number
                                        HAVING
                                            COUNT(number) = 2
                                        ";

        try {
            $stmt = DB::get()->dbh->prepare($intersectionSelectQuery);
            $stmt->execute();

            while($rec = $stmt->fetch()) {

                $comSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telecommunications (set_name, caller, called, time_stamp, duration) 
                                    VALUES ('".$newSet."', '".$rec->caller."', '".$rec->called."', '".$rec->time_stamp."', ".$rec->duration."); ";

                $telSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) 
                                    VALUES ('".$newSet."', '".$rec->caller."'), ('".$newSet."', '".$rec->called."') ";

                try{
                    $stmt2 = DB::get()->dbh->prepare($comSetQuery);
                    $stmt2->execute();
                }catch(PDOException $e) {
                    die(json_encode(array("error" => "Intersection insert 1 query failed: " . $e->getMessage())));
                }

                try{
                    $stmt3 = DB::get()->dbh->prepare($telSetQuery);
                    $stmt3->execute();
                }catch(PDOException $e) {
                    die(json_encode(array("error" => "Intersection insert 2 query failed: " . $e->getMessage())));
                }

            } //end of while loop

            $stmt = DB::get()->dbh->prepare($telephonesIntersectionQuery);
            $stmt->execute();

            while($rec = $stmt->fetch()) {

                $telSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number)
                                    VALUES ('".$newSet."', '".$rec->number."') ";

                try{
                    $stmt4 = DB::get()->dbh->prepare($telSetQuery);
                    $stmt4->execute();
                }catch(PDOException $e) {
                    die(json_encode(array("error" => "Intersection insert 3 query failed: " . $e->getMessage())));
                }

            } //end of while loop

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Intersection select query failed: " . $e->getMessage())));
        }

    } //end of intersection mode

    if($mode == "asymmetric_difference") {

        $setA = $sets[0];
        $setB = $sets[1];

        $query = "
                
                SELECT 
                  caller, called, time_stamp, duration
                FROM
                  " . Config::read('mysql.prefix') . "datasets_telecommunications
                WHERE
                  set_name = '".$setA."' OR set_name = '".$setB."'
                GROUP BY
                    caller, called, time_stamp, duration
                HAVING
                (caller, called, time_stamp, duration) NOT IN                
                    (SELECT
                        TA.caller AS caller, TA.called AS called, TA.time_stamp AS time_stamp, TA.duration AS duration
                      FROM
                      
                          (SELECT 
                            * 
                          FROM 
                            " . Config::read('mysql.prefix') . "datasets_telecommunications 
                          WHERE 
                            set_name = '".$setA."' ) AS TA 
                        
                      JOIN 
                        
                          (SELECT
                            *
                          FROM
                            " . Config::read('mysql.prefix') . "datasets_telecommunications 
                          WHERE
                            set_name = '".$setB."'
                            ) AS TB
                        
                      ON                  
                        TA.caller = TB.caller AND TA.called = TB.called AND TA.time_stamp = TB.time_stamp AND TA.duration = TB.duration)
                
        ";

        $newSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telecommunications (set_name, caller, called, time_stamp, duration) VALUES ";
        $callsCounter = 0;

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();

            //finish building the insert query
            while($rec = $stmt->fetch()) {
                $newSetQuery .= "('".$newSet."', '".$rec->caller."', '".$rec->called."', ".$rec->time_stamp.", ".$rec->duration."), ";
                $callsCounter++;
            }

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Query 1 failed: " . $e->getMessage())));
        }

        $newSetQuery = substr($newSetQuery, 0, strlen($newSetQuery)-2);

        if($callsCounter > 0) {
            try {
                $stmt = DB::get()->dbh->prepare($newSetQuery);
                $stmt->execute();
            } catch (PDOException $e) {
                die(json_encode(array("error" => "Query 2 failed: " . $e->getMessage())));
            }
        }

        //now the telephone numbers
        $query = "
                    SELECT 
                      number
                    FROM
                      " . Config::read('mysql.prefix') . "datasets_telephone_numbers
                    WHERE
                      set_name = '".$setA."' OR set_name = '".$setB."' 
                    GROUP BY
                        number
                    HAVING
                        number 
                    NOT IN                    
                        (SELECT
                            TA.number AS number
                          FROM
                          
                              (SELECT 
                                * 
                              FROM 
                                " . Config::read('mysql.prefix') . "datasets_telephone_numbers 
                              WHERE 
                                set_name = '".$setA."' ) AS TA 
                            
                          JOIN 
                            
                              (SELECT
                                *
                              FROM
                                " . Config::read('mysql.prefix') . "datasets_telephone_numbers 
                              WHERE
                                set_name = '".$setB."'
                                ) AS TB
                            
                          ON                  
                            TA.number = TB.number)
                    ";

        $newSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) VALUES ";
        $numbersCounter = 0;

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();

            //finish building the insert query
            while($rec = $stmt->fetch()) {
                $newSetQuery .= "('".$newSet."', '".$rec->number."'), ";
                $numbersCounter++;
            }

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Query 3 failed: " . $e->getMessage())));
        }

        $newSetQuery = substr($newSetQuery, 0, strlen($newSetQuery)-2);

        if($numbersCounter > 0) {
            try {
                $stmt = DB::get()->dbh->prepare($newSetQuery);
                $stmt->execute();
            } catch (PDOException $e) {
                die(json_encode(array("error" => "Query 4 failed: " . $e->getMessage())));
            }
        }

    } //end of asymmetric difference mode

    if($mode == "subtracting") {

        $setA = $sets[0];
        $setB = $sets[1];

        $query = "
                SELECT
                  TA.caller, TA.called, TA.time_stamp, TA.duration
                FROM                
                    (SELECT 
                      caller, called , time_stamp, duration
                    FROM
                      " . Config::read('mysql.prefix') . "datasets_telecommunications
                    WHERE
                      set_name = '".$setA."' ) AS TA                  
                LEFT OUTER JOIN                
                    (SELECT 
                      caller, called , time_stamp, duration
                    FROM
                      " . Config::read('mysql.prefix') . "datasets_telecommunications
                    WHERE
                      set_name = '".$setB."' ) AS TB
                ON 
                    TA.caller = TB.caller AND TA.called = TB.called AND TA.time_stamp = TB.time_stamp AND TA.duration = TB.duration
                WHERE
                    TB.caller IS null                
        ";

        $newSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telecommunications (set_name, caller, called, time_stamp, duration) VALUES ";
        $callsCounter = 0;

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();

            //finish building the insert query
            while($rec = $stmt->fetch()) {
                $newSetQuery .= "('".$newSet."', '".$rec->caller."', '".$rec->called."', ".$rec->time_stamp.", ".$rec->duration."), ";
                $callsCounter++;
            }

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Query 1 failed: " . $e->getMessage())));
        }

        $newSetQuery = substr($newSetQuery, 0, strlen($newSetQuery)-2);

        if($callsCounter > 0) {
            try {
                $stmt = DB::get()->dbh->prepare($newSetQuery);
                $stmt->execute();
            } catch (PDOException $e) {
                die(json_encode(array("error" => "Query 2 failed: " . $e->getMessage())));
            }
        }

        //now the telephone numbers
        $query = "

                SELECT
                  TA.number
                FROM                
                    (SELECT 
                      number
                    FROM
                      " . Config::read('mysql.prefix') . "datasets_telephone_numbers
                    WHERE
                      set_name = '".$setA."' ) AS TA                  
                LEFT OUTER JOIN                
                    (SELECT 
                      number
                    FROM
                      " . Config::read('mysql.prefix') . "datasets_telephone_numbers
                    WHERE
                      set_name = '".$setB."' ) AS TB
                ON
                    TA.number = TB.number
                WHERE
                    TB.number IS null
                    ";

        $newSetQuery = "INSERT IGNORE INTO " . Config::read('mysql.prefix') . "datasets_telephone_numbers (set_name, number) VALUES ";
        $numbersCounter = 0;

        try {
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->execute();

            //finish building the insert query
            while($rec = $stmt->fetch()) {
                $newSetQuery .= "('".$newSet."', '".$rec->number."'), ";
                $numbersCounter++;
            }

        } catch(PDOException $e) {
            die(json_encode(array("error" => "Query 3 failed: " . $e->getMessage())));
        }

        $newSetQuery = substr($newSetQuery, 0, strlen($newSetQuery)-2);

        if($numbersCounter > 0) {
            try {
                $stmt = DB::get()->dbh->prepare($newSetQuery);
                $stmt->execute();
            } catch (PDOException $e) {
                die(json_encode(array("error" => "Query 4 failed: " . $e->getMessage())));
            }
        }

    } //end of subtracting mode

    $setRetrievalQuery = "SELECT * FROM " . Config::read('mysql.prefix') . "datasets WHERE name=:nam";

    try {
        $stmt = DB::get()->dbh->prepare($setRetrievalQuery);
        $stmt->bindParam(":nam", $newSet, PDO::PARAM_STR);
        $stmt->execute();
    } catch(PDOException $e) {
        die(json_encode(array("error" => "Query set retrieval failed: " . $e->getMessage())));
    }

    $data = $stmt->fetch();

    $result['data']['name'] = $data->name;
    $result['data']['infoLine'] = $data->creation_timestamp . " created by " . $data->creator_username;

    die(json_encode(array("reply" => $result)));

}

?>