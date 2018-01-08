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
	if($_POST["type"] !== null && $_POST["type"] === "confirm"){
		// echo("We inside the type");	
		// print_r($_POST);
		if(!is_null($_POST["inviteCode"]) && is_string($_POST["inviteCode"])){
			$cmd = "php -f ../rsvp/rsvp.php ".$method." ".trim($_POST["inviteCode"])." ".$_POST["attending"]." ".$_POST["num_attending"]." \"".addslashes($_POST["notes"])."\"";
			
			// echo("Calling ".$cmd."\n");
			echo(exec($cmd)); 
		}
	} else {
		echo("shiz failed, cuz");
	}
}
