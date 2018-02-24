<?php
date_default_timezone_set('America/Los_Angeles');
require_once __DIR__ . '/../rsvp-config.php';

$allowed = $ALLOWED_ORIGINS;
if(isset($_SERVER['HTTP_ORIGIN'])){
	in_array($_SERVER['HTTP_ORIGIN'], $allowed); //this isn't doing anything rn...
	header("Access-Control-Allow-Origin: ".$_SERVER['HTTP_ORIGIN']);
	header("Access-Control-Allow-Methods: GET, POST");	
}
// include_once(__DIR__ . '/../rsvp/rsvp.php');
// print_r($_SERVER);

if(!isset($_SERVER["SERVER_NAME"])){ //if running in cli for testing...
	parse_str(implode('&', array_slice($argv, 1)), $_GET);	
}

$method = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], array("GET", "POST")) ? $_SERVER['REQUEST_METHOD'] : "GET";
// echo("Method: ".$method."\n");
// echo("Server request method is ". $_SERVER['REQUEST_METHOD']."\n");
// print_r($_GET);



if($method === "GET"){
	// echo("We inside the get\n");
	if($_GET["type"] !== null && $_GET["type"] === "lookup"){
		// echo("We inside the type");
		if(!is_null($_GET["query"]) && is_string($_GET["query"])){
			$cmd = "php -f ../rsvp/rsvp.php ".$method." ".trim($_GET["query"]);
			// echo("Calling ".$cmd."\n");
			echo(exec($cmd));
		}
	} else {
		echo("shiz failed, cuz");
	}
}

if($method === "POST"){
	error_log("POST Variables:");
	error_log($_POST["inviteCode"]."\n");
	error_log($_POST["type"]);
	if($_POST["type"] !== null && $_POST["type"] === "confirm"){
		$taskId 	= time();
		$tasks_path = sprintf("%s/tasks", __DIR__);
		$filename 	= sprintf("%s/%d.json", $tasks_path, $taskId); //timestamped task id
		// echo($filename);
		$data = array(
			"task_id" 			=> $taskId,
			"invite_code" 		=> trim($_POST["inviteCode"]),
			"attending" 		=> ((isset($_POST["attending"]) && $_POST["attending"] == "yes") ? "Y" : "N"),
			"num_attending" 	=> intval($_POST["num_attending"]),
			"attending_welcome" => isset($_POST["attending_welcome"]) ? $_POST["attending_welcome"] : null,
			"attending_brunch"	=> isset($_POST["attending_brunch"]) ? $_POST["attending_brunch"] : null,
			"shuttle" 			=> isset($_POST["shuttle"]) ? $_POST["shuttle"] : null,
			"notes"				=> addslashes($_POST["notes"])
		);
		// 

		// print_r($data);
		// exit();
		
		if(!file_exists($tasks_path)){ //create tasks folder if one doesn't already exist
			// $old = umask(0);
			mkdir($tasks_path, 0770);
			// umask($old);
		}
		if(file_exists($tasks_path)){ //defaults to 0777
			if(file_put_contents($filename, json_encode($data))){
				// echo("File written!: \n");
				// print_r($_POST);
				if(!is_null($data["invite_code"]) && is_string($data["invite_code"])){
					$cmd = sprintf("php -f ../rsvp/rsvp.php %s %d", "POST", $taskId);
					//$cmd = "php -f ../rsvp/rsvp.php ".$method." ".trim($_POST["inviteCode"])." ".$_POST["attending"]." ".$_POST["num_attending"]." \"".addslashes($_POST["notes"])."\"";
					echo(exec($cmd)); 
				}
			} else {
				echo("Unable to write task file. Tell Danielle or Eric!");
			}
		} else {
			echo("Unable to create tasks directory.");
		}
	} else {
		echo("shiz failed, cuz");
	}
}
