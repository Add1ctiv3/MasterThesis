<?php

if (!defined("__ROOTFILE__")) { define("__ROOTFILE__", true); }
if (!defined("__AJAXFILE__")) { define("__AJAXFILE__", true); }

if (!file_exists(dirname(__FILE__) . "/../includes/boot.php")) { die("Boot file was not found!"); }
include_once(dirname(__FILE__) . "/../includes/boot.php");

if(!User::isLoggedIn()) { die(json_encode(array("error" => "You have to log in!"))); }

if($_POST['uri'] == "importFromDataset") {
	
	$result = array("result" => "success",
					"message" => "");

    if(isset($_POST['set']) && !empty($_POST['set'])) {
        $set = $_POST['set'];
    }

    //first get the user logged in
    $user = User::getLoggedUser();

    //first get the dataset from the database to check for rights to access
    $getSetQuery = "SELECT * FROM ix_datasets WHERE name = :nam";

    try{

        $stmt = DB::get()->dbh->prepare($getSetQuery);
        $stmt->bindParam(":nam", $set, PDO::PARAM_STR);
        $stmt->execute();

        $fetch = $stmt->fetch();

        if(!$fetch) {
            if($user['type'] != "administrator" && $user['username'] != $fetch->creator_username && $fetch->visibility != "public") {
                $result['result'] = "failure";
                $result['message'] = "You do not have the rights to this datasets content!";
                die(json_encode(array("reply" => $result)));
            }
        }

    }catch(PDOException $e) {
        die(json_encode(array("error" => "Select dataset query failed!")));
    }

    //now create a visualizer
    $vis = new Visualizer($set);
    $data = $vis->visualizeDataset("KEY_PLAYER");

    if($data['result'] != "success") {
        $result['result'] = "failure";
        $result['message'] = $data['message'];
        die(json_encode(array("reply" => $result)));
    }

    $result['data'] = $data['data'];

	die(json_encode(array("reply" => $result)));
	
}

if($_POST['uri'] == "expandNodes") {

    $result = array("result" => "success",
        "message" => "");

    if(isset($_POST['nodes']) && !empty($_POST['nodes'])) {
        $nodes = $_POST['nodes'];
    } else {
        $result['result'] = "failure";
        $result['message'] = "You need to supply the nodes to be expanded...";
        die(json_encode(array("reply" => $result)));
    }

    //now create a visualizer
    $data = Visualizer::expandNodes($nodes);

    if($data['result'] != "success") {
        $result['result'] = "failure";
        $result['message'] = $data['message'];
        die(json_encode(array("reply" => $result)));
    }

    $result['data'] = $data['data'];

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "recordDetails") {

    $result = array("result" => "success",
        "message" => "");

    if(isset($_POST['node'])) {

        $number = $_POST['node'];

        $query = "SELECT
                    TEL.*
                  FROM
                    " . Config::read('mysql.prefix') . "telephone_number AS TEL
                  WHERE
                    TEL.number = :num
                  ";

        try{
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->bindParam(":num", $number, PDO::PARAM_STR);
            $stmt->execute();

            $f = $stmt->fetch();

            $data = array(
                "number" => $f->number,
                "ccode" => $f->country_code,
                "type" => $f->type,
                "creation" => $f->creation_date,
                "num_weight" => $f->weight,
                "record_type" => "telephone"
            );

            $result['data'] = $data;
            die(json_encode(array("reply" => $result)));

        }catch(PDOException $e) {
            die(json_encode(array("error" => "Select telephones query failed!")));
        }

    } //end of node mode

    $result['result'] = "failure";
    $result['message'] = "You need to double click on a telephone node.";
    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "edgeDetails") {

    $result = array("result" => "success",
        "message" => "");

    if(isset($_POST['node1']) && isset($_POST['node2'])) {

        $node_1 = $_POST['node1'];
        $node_2 = $_POST['node2'];

        $query = "SELECT
                    *
                  FROM
                    " . Config::read('mysql.prefix') . "telecommunications
                  WHERE
                    (telephone_1 = :num1 && telephone_2 = :num2) || (telephone_2 = :num3 && telephone_1 = :num4)
                  ";

        try{
            $stmt = DB::get()->dbh->prepare($query);
            $stmt->bindParam(":num1", $node_1, PDO::PARAM_STR);
            $stmt->bindParam(":num2", $node_2, PDO::PARAM_STR);
            $stmt->bindParam(":num3", $node_1, PDO::PARAM_STR);
            $stmt->bindParam(":num4", $node_2, PDO::PARAM_STR);
            $stmt->execute();

            $f = $stmt->fetch();

            $calls = array();

            while($com = $stmt->fetch()) {
                $call = array(
                    "caller" => $com->telephone_1,
                    "called" => $com->telephone_2,
                    "timestamp" => intToDateAndTime($com->time_stamp),
                    "stamp" => $com->time_stamp,
                    "duration" => $com->duration,
                    "weight" => $com->weight,
                    "type" => $com->type,
                    "creation_date" => $com->creation_date,
                    "record_type" => "telecommunication"
                );
                array_push($calls, $call);
            }

            $result['data'] = $calls;

            $result['total_records'] = count($calls);

            die(json_encode(array("reply" => $result)));

        }catch(PDOException $e) {
            die(json_encode(array("error" => "Select telecommunications query failed!" . $e->getMessage())));
        }

    } //end of edge mode

    $result['result'] = "failure";
    $result['message'] = "You need to double click on a telephone or a communication node or edge.";
    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "networkCapitalAlgorithm") {

    $result = array("result" => "success",
                    "message" => "",
                    "data" => array()
                    );

    $nodes = $_POST['nodes'];
    $edges = $_POST['edges'];
    $RSL = $_POST['rsl'];

    //create a graph to be able to use dijkstra algorithm
    $graph = createNodesGraph($nodes);
    $dijkstra = new Dijkstra($graph);

    $analysis = networkCapitalAnalysis($nodes, $edges, $RSL, $dijkstra);

    $result['data']['FIRST_ITERATION'] = array(
                                            "NODE" => $analysis['node_to_remove'],
                                            'NODE_CAPITAL' => $analysis['node_capital'],
                                            'NETWORK_CAPITAL' => $analysis['NC'],
                                            'NODE_SCORES' => $analysis['node_scores']
                                        );

    $removal_1 = removeNodeFromArrays($result['data']['FIRST_ITERATION']['NODE'], $nodes, $edges);
    $nodes = $removal_1['nodes'];
    $edges = $removal_1['edges'];

    $graph = createNodesGraph($nodes);
    $dijkstra = new Dijkstra($graph);

    $analysis = networkCapitalAnalysis($nodes, $edges, $RSL, $dijkstra);

    $result['data']['SECOND_ITERATION'] = array(
                                                "NODE" => $analysis['node_to_remove'],
                                                'NODE_CAPITAL' => $analysis['node_capital'],
                                                'NETWORK_CAPITAL' => $analysis['NC'],
                                                'NODE_SCORES' => $analysis['node_scores']
                                            );

    $removal_2 = removeNodeFromArrays($result['data']['SECOND_ITERATION']['NODE'], $nodes, $edges);
    $nodes = $removal_2['nodes'];
    $edges = $removal_2['edges'];

    $graph = createNodesGraph($nodes);
    $dijkstra = new Dijkstra($graph);

    $analysis = networkCapitalAnalysis($nodes, $edges, $RSL, $dijkstra);

    $result['data']['THIRD_ITERATION'] = array(
                                            "NODE" => $analysis['node_to_remove'],
                                            'NODE_CAPITAL' => $analysis['node_capital'],
                                            'NETWORK_CAPITAL' => $analysis['NC'],
                                            'NODE_SCORES' => $analysis['node_scores']
                                        );

    $removal_3 = removeNodeFromArrays($result['data']['THIRD_ITERATION']['NODE'], $nodes, $edges);
    $nodes = $removal_3['nodes'];
    $edges = $removal_3['edges'];

    $graph = createNodesGraph($nodes);
    $dijkstra = new Dijkstra($graph);

    $analysis = networkCapitalAnalysis($nodes, $edges, $RSL, $dijkstra);

    $result['data']['FOURTH_ITERATION'] = array(
        "NODE" => $analysis['node_to_remove'],
        'NODE_CAPITAL' => $analysis['node_capital'],
        'NETWORK_CAPITAL' => $analysis['NC'],
        'NODE_SCORES' => $analysis['node_scores']
    );

    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "networkFragmentationAlgorithm") {

    $result = array("result" => "success",
        "message" => "",
        "data" => array()
    );

    $nodes = $_POST['nodes'];

    $couples = array();

    //create a graph to be able to use dijkstra algorithm
    $graph = createNodesGraph($nodes);
    $dijkstra = new Dijkstra($graph);

    for($i = 0; $i < count($nodes); $i++) {
        for($j = $i + 1; $j < count($nodes); $j++) {
            $key = $nodes[$i]['id'] . "-" . $nodes[$j]['id'];
            $inversedKey = $nodes[$j]['id'] . "-" . $nodes[$i]['id'];
            if(!isset($couples[$key]) && !isset($couples[$inversedKey])) {
                $paths = $dijkstra->shortestPaths($nodes[$i]['id'], $nodes[$j]['id']);
                if(count($paths) == 0) {
                    $couples[$key] = 0;
                }else {
                    $len = count($paths[0]);
                    $couples[$key] = ($len==0) ? 0 : ($len-1);
                }
            }
        }
    }

    //by now we should have an array with all possible couples and their distance
    //now i need to iterate every node and calculate the fragmentation for every node and store the results in an array. The max fragmentation gets picked
    $resultsArray = array();
    $initialNF = 0;

    for($i = -1 ; $i < count($nodes) ; $i++) {

        //create an array and remove one node from it so we can calculate NF
        if($i >= 0) {
            $newNodes = array_merge(array(), $nodes);
            $nodeInQuestion = $newNodes[$i]['id'] . "";
            array_splice($newNodes, $i, 1);
        } else {
            $newNodes = $nodes;
        }

        $NF = 0;
        $n = count($newNodes);
        $distancesSum = 0;

        for($j = 0; $j < count($newNodes); $j++) {

            for($k = $j + 1; $k < count($newNodes); $k++) {

                $key = $newNodes[$j]['id'] . "-" . $newNodes[$k]['id'];
                $distancesSum += 1/$couples[$key];

            }

        }// finished the iteration that gives us the distances sum

        $NF = 1 - ((2*$distancesSum)/($n*($n-1)));

        if($i >= 0) {
            $resultsArray[$nodeInQuestion] = $NF;
        } else {
            $initialNF = $NF;
        }

    }

    $maxArray = array();
    $maxValue = 0;
    foreach($resultsArray as $node => $score) {
        if($score == $maxValue) {
            array_push($maxArray, array("node" => $node, "score" => $score));
        }
        if($score > $maxValue) {
            $maxValue = $score;
            $maxArray = array(array("node" => $node, "score" => $score));
        }

    }

    $result['data'] = $maxArray;
    $result['initialNF'] = $initialNF;
    die(json_encode(array("reply" => $result)));

}

if($_POST['uri'] == "networkReachAlgorithm") {

    $result = array("result" => "success",
        "message" => "",
        "data" => array()
    );

    $nodes = $_POST['nodes'];

    //create a graph to be able to use dijkstra algorithm
    $graph = createNodesGraph($nodes);
    $dijkstra = new Dijkstra($graph);

    $data = array();

    for($i = 0; $i < count($nodes); $i++) {

        //for each node i calculate its reach
        $sum = 0;

        for($j = 0; $j < count($nodes); $j++) {
            if($nodes[$i]['id'] == $nodes[$j]['id']) { continue; }
            $distance = 0;
            $paths = $dijkstra->shortestPaths($nodes[$i]['id'], $nodes[$j]['id']);
            if(!count($paths) == 0) {
                $distance = 1/(count($paths[0])-1);
                $sum += $distance;
            }
        } //end of all other nodes loop

        $R = $sum / count($nodes);
        $data[$nodes[$i]['id']] = $R;

    }

    arsort($data);
    $retData = array_slice($data,0 ,3, true);

    foreach($retData as $key => $val) {
        array_push($result['data'], array("number" => $key, "value" => $val));
    }

    die(json_encode(array("reply" => $result)));

}

function networkCapitalAnalysis($nodes, $edges, $RSL, $dijkstra) {

    $result = array();

    $network_capital = 0;

    $max_node = 0;
    $nodes_to_return = array();
    //$log = "";

    //nodes iteration
    foreach($nodes as $NODE) {

        //we know that NC (network capital) = (Node_Scores + Connection_Scores) / ( N + [N*(N-1)*RSL] )
        $NODE_SCORE = $NODE['weight'];
        $LINK_SCORE = 0;

        //$log .= "\n\n\n\n\n\n\n\n\n";
        //$log .= "CALCULATING CAPITAL FOR NODE: ".$NODE['id']."\n";

        //for every other node calculate the shortest path
        foreach($nodes as $n) {

            //exclude this node
            if($n['id'] == $NODE['id']) {
                continue;
            }

            //$log .= "\n";
            //$log .= "Calculating shortest path for node -> NODE: " . $n['id'] . "\n";

            //find the shortest path
            $paths = $dijkstra->shortestPaths($NODE['id'], $n['id']);

            if(count($paths) > 0) {

                //$log .= "There is a shortest path which is: " . print_r($paths[0], true). "\n";

                //then there is at least one shortest path
                $shortestPath = $paths[0]; //B -> A -> C -> E

                //now try to calculate each links contribution to capital
                //$log .= "--Reseting link_scores array\n";
                unset($link_scores);
                $link_scores = array();

                //path nodes iteration
                for($i = 0; $i < count($shortestPath); $i++) {

                    //$log .= "--Path Point: " . $shortestPath[$i] . "\n";

                    //exclude the starting node
                    if($shortestPath[$i] == $NODE['id']) {
                        //$log .= "-- escaping\n";
                        continue;
                    }

                    $from = $shortestPath[$i-1];
                    $to = $shortestPath[$i];
                    $link = getLinkFromArray($from, $to, $edges);

                    $weight = $link['weight'];

                    $score = 0;
                    if($i == 1) {
                        //then we are calculating the direct link score
                        $score = round($NODE_SCORE * $weight * $RSL, 9);
                        //$log .= "--Score = NODE_SCORE: " . $NODE_SCORE . " * weight: " . $weight . " * RSL: " . $RSL . " = ".$score."\n";

                        $link_scores[$from . "-" . $to] = $score;
                        //$log .= "----Putting this score in the link_scores array under the key -> " . $from . "-" . $to . "\n";
                        //if there is only a direct link then and only then add the direct links score
                        if(count($shortestPath) == 2) {
                            $LINK_SCORE += $score;
                            //$log .= "--This is a direct link so adding to link_score: ".$score." \n";
                        }

                    } else {
                        //we are calculating the indirect link
                        //i = 2 from = a kai to = c
                        $score = round($link_scores[$shortestPath[$i-2] . "-" . $from] * $weight * $RSL, 9);
                        //$log .= "--Score = PREVIOUS LINK (".$shortestPath[0] . "-" . $from."): " . $link_scores[$shortestPath[0] . "-" . $from] . " * weight: " . $weight . " * RSL: " . $RSL . " = ".$score."\n";

                        $link_scores[$shortestPath[0] . "-" . $to] = $score;
                        //$log .= "----Putting this score in the link_scores array under the key -> " . $shortestPath[0] . "-" . $to . "\n";

                        if($shortestPath[$i] == $shortestPath[count($shortestPath)-1]) {
                            $LINK_SCORE += $score;
                            //$log .= "--Adding score: " . $score;
                        }

                    }

                    //$log .= "\n";

                } //end of each path link iteration

            } //end of if count($paths) > 0 block

        }//end of foreach other node iteration

        //$log .= "\nNode capital (added in NC) = ".$NODE_SCORE." + ".$LINK_SCORE." \n\n";
        $node_capital = $NODE_SCORE + $LINK_SCORE;

        if(($node_capital/7) > $max_node) {
            $max_node = ($node_capital/7);
            $result['node_to_remove'] = $NODE['id'];
            $result['node_capital'] = $max_node;
        }

        $network_capital += $node_capital;

        array_push($nodes_to_return, array("id" => $NODE['id'], "capital" => ($node_capital / 7)));

    } //end of nodes iteration

    $n = count($nodes);
    $NC = $network_capital / ($n + ($n*($n-1)*$RSL));

    $result['node_scores'] = $nodes_to_return;
    $result['NC'] = $NC;

    return $result;

}

function getLinkFromArray($to, $from, $array) {

    foreach($array as $edge) {
        if(($edge['from'] == $from && $edge['to'] == $to) || ($edge['from'] == $to && $edge['to'] == $from)) {
            return $edge;
        }
    }

    return null;

}

function removeNodeFromArrays($nodeId, $nodes, $edges) {

    $Nodes = $nodes;
    $Edges = $edges;

    for($i = 0; $i < count($Nodes); $i++) {
        if($Nodes[$i]['id'] == $nodeId) {
            //remove $nodes[$i]
            array_splice($Nodes, $i, 1);
        }
    }

    for($i = 0; $i < count($Edges); $i++) {
        if($Edges[$i]['from'] == $nodeId || $Edges[$i]['to'] == $nodeId) {
            //remove $nodes[$i]
            array_splice($Edges, $i, 1);
        }
    }

    return array("nodes" => $Nodes, "edges" => $edges);

}

function createNodesGraph($nodes) {
    $graph = array();
    foreach($nodes as $n) {

        $connections = $n['connections']; //array of arrays with from, to, value
        $graph[$n['id']] = array();

        foreach($connections as $link) {

            $fromId = $link['from'];
            $toId = $link['to'];
            $weight = $link['value'];

            if($n['id'] == $fromId) {
                $graph[$n['id']][$toId] = (1/$weight); // i reverse my weight here so i can use dijkstra (weight = speed but the opposite)
            }
            if($n['id'] == $toId) {
                $graph[$n['id']][$fromId] = $weight;
            }

        }
    }

    return $graph;
}

?>