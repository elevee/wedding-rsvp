<?php
date_default_timezone_set('America/Los_Angeles');
header('Content-type:application/json;charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../rsvp-config.php';

define('APPLICATION_NAME', 'Wedding RSVP');
define('CREDENTIALS_PATH', __DIR__ . '/.credentials/weddingRSVP.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS)
));
// echo(CREDENTIALS_PATH);
// echo(CLIENT_SECRET_PATH);
// exit();
if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');
  // $client->setRedirectUri('http://www.levinelavidaloca.com/');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}
// ------------------------------------------------

// parse_str(implode('&', array_slice($argv, 1)), $_GET);
// echo("GET VARS:\n");
// print_r($_GET);

// Get the API client and construct the service object.
$client = getClient();

// $client = new Google_Client();
// $client->setApplicationName(APPLICATION_NAME);
// $client->setScopes(SCOPES);
// $api_key = $API_KEY;
// $client->setDeveloperKey($api_key);
// $client->setAccessType("offline");
$service = new Google_Service_Sheets($client);

// Get the values from the spreadsheet
$spreadsheetId = $SPREADSHEET_ID;
$range = 'Guestlist!A:T';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();

// if (count($values) == 0) {
//   print "No data found.\n";
// } else {
//   print "Name, Party:\n";
//   foreach ($values as $row) {
//     // Print columns A and B, which correspond to indices 0 and 1.
//     printf("%s, %s\n", $row[0], $row[1]);
//   }
// }

$method = isset($argv[1]) && is_string($argv[1]) && strlen($argv[1]) > 0 ? $argv[1] : null;
//isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], array("GET", "POST")) ? $_SERVER['REQUEST_METHOD'] : "GET"
$inviteCode 	= (isset($argv[2]) && is_string($argv[2]) && strlen($argv[2]) > 0) ? $argv[2] : null;
$zipCode		= null;
if($method == "GET"){
	$zipCode = (isset($argv[3]) && is_string($argv[3]) && strlen($argv[3]) > 0) ? $argv[3] : null;
}
$attending 		= (isset($argv[3]) && is_string($argv[3]) && strlen($argv[3]) > 0) ? $argv[3] : null;
$num_attending 	= (isset($argv[4]) && is_string($argv[4]) && strlen($argv[4]) > 0) ? $argv[4] : null;
$notes 			= (isset($argv[5]) && is_string($argv[5]) && strlen($argv[5]) > 0) ? $argv[5] : null;



// $writeRange = "Guestlist!L2:O2";
// // echo("What's the write range?  ". $writeRange);
// $vals = [
//     ["N", 2, date('Y-m-d H:i:s'), "blah"] //Yes or No, size of party, time replied, notes
// ];
// //(isset($notes) && is_string($notes) && strlen($notes)>0 ? $notes : null)
// $body = new Google_Service_Sheets_ValueRange([
//   'values' => $vals
// ]);
// $params = [
//   'valueInputOption' => "USER_ENTERED"
// ];
// // echo("SPREADSHEET_ID is: ". $spreadsheetId);
// // echo("writeRange is: ". $writeRange);
// // echo("params is: ". $params);
// // echo("attending is: ". $attending);
// // echo("num_attending is: ". $num_attending);
// // exit();
// $result = $service->spreadsheets_values->update($spreadsheetId, $writeRange, $body, $params);
// printf("%d cells updated.", $result->getUpdatedCells());

// exit();


function confirm($task){
	global $spreadsheetId, $service, $values;
	if(isset($task["attending"]) && is_string($task["attending"]) /* && isset($task["num_attending"]) && is_numeric($task["num_attending"])*/){
		if(isset($task["invite_code"]) && is_string($task["invite_code"]) && strlen($task["invite_code"]) > 0){
			// $service = $initSheet();
			foreach ($values as $i => $row) {
				if (isset($row[10]) && $row[10] == $task["invite_code"]){
					// echo("Row: ". $i."\n");
					$r = $i+1; //true row number (accounting for header row)
					$writeRange = "Guestlist!E".$r.":T".$r;
					// echo("What's the write range?  ". $writeRange);
					$guest_coming = isset($task["attending"]) && $task["attending"] == "Y";
					$vals = null;
					if($guest_coming){
						$vals = [[
							isset($task["email"]) && strlen($task["email"]) > 0 ? $task["email"] : Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							$task["attending"], 
						    $task["num_attending"],
						    date('Y-m-d H:i:s'), 
						    stripcslashes($task["notes"]), //Yes or No, size of party, time replied, notes
						    $task["attending_welcome"],
						    $task["shuttle"],
						    Google_Model::NULL_VALUE,
						    Google_Model::NULL_VALUE,
						    $task["attending_brunch"]
						]];
					} else { //guest not attending
						$vals = [[
							isset($task["email"]) && strlen($task["email"]) > 0 ? $task["email"] : Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							Google_Model::NULL_VALUE,
							"N", //$task["attending"], 
						    0, //$task["num_attending"],
						    date('Y-m-d H:i:s'), 
						    stripcslashes($task["notes"]), //Yes or No, size of party, time replied, notes
						    "N", //$task["attending_welcome"],
						    "", //shuttle (overwriting if previously filled)
						    Google_Model::NULL_VALUE,
						    Google_Model::NULL_VALUE,
						    "N"
						]];
					}
					//(isset($notes) && is_string($notes) && strlen($notes)>0 ? $notes : null)
					$body = new Google_Service_Sheets_ValueRange([
					  'values' => $vals
					]);
					$params = [
					  'valueInputOption' => "USER_ENTERED"
					];
					// echo("SPREADSHEET_ID is: ". $spreadsheetId);
					// echo("writeRange is: ". $writeRange);
					// echo("params is: ". $params);
					// echo("attending is: ". $task["attending"]);
					// echo("num_attending is: ". $task["num_attending"]);
					// exit();
					$result = $service->spreadsheets_values->update($spreadsheetId, $writeRange, $body, $params);
					// echo($result->updatedData);
					// printf("%d cells updated.", $result->getUpdatedCells());


					$output = array(
						"status" 			=> "SUCCESS",
						"user"				=> array(
							"invite_code"	=> $row[10]
						),
						"responseHeadline" 	=> "Thank you! Your RSVP has been recorded.",
						"responseText" 		=> ($guest_coming ? "Looking forward to seeing you up in Paso!" : "We will miss your presence!")
					);
					echo json_encode($output);
				}
			}
		}
	} else {
		echo("Either attending or num_attending is not formatted correctly");
	}
	// $attending 			= "N";//(isset($_POST["attending"]) && is_bool($_POST["attending"]) ? $_POST["attending"] : null);
	// $num_attending 		= 50;//(isset($_POST["size"]) && strlen(trim($_POST["size"])) > 0 ? trim($_POST["size"]) : null);
}

function lookup($invite_code, $zip_code){
	global $values;
	if(isset($invite_code) && is_string($invite_code) && strlen($invite_code) > 0 && isset($zip_code) && is_string($zip_code) && strlen($zip_code) > 0){
		// $service = initSheet();
		if (count($values) == 0) {
		  	print "No data found.\n";
		} else {
			$shuttles = lookupShuttles();
			foreach ($values as $row) {
				if ((isset($row[10]) && $row[10] == $invite_code) && (isset($row[8]) && substr($row[8], 0, 5) == substr($zip_code, 0, 5))){
					// printf("%s has the code %s\n", $row[0], $inviteCode);
					// print_r($row);
					// echo("YAY");

					$output = array(
						"status" => "SUCCESS",
						"shuttles" => $shuttles,
						"record" => array(
							"name" 	 			=> $row[0],
							"size" 	 			=> $row[1],
							"code" 	 			=> $row[10],
							"email"				=> $row[4],
							"attending" 		=> isset($row[11]) ? $row[11] : null,
							"num_attending" 	=> isset($row[12]) ? $row[12] : null,
							"attending_welcome" => isset($row[15]) ? $row[15] : null,
							"attending_brunch"  => isset($row[19]) ? $row[19] : null,
							"shuttle"			=> isset($row[16]) ? $row[16] : null,
							"notes"				=> isset($row[14]) ? $row[14] : null,
						)
					);
					echo json_encode($output);
					exit();
				} 
			}
		}
	}
	$output = array(
		"status"	=> "ERROR",
		"message"	=> "No guest(s) found under that invite code/zip code combination. Please check spelling and try again."
	);
	echo json_encode($output);
}

function lookupShuttles(){
	global $service, $spreadsheetId;
	$shuttles = array();
	$shuttle_response = $service->spreadsheets_values->get($spreadsheetId, "Guestlist!AB6:AF7");
	foreach ($shuttle_response as $row) {
		$shuttles[] = array(
			"number" 	=> $row[0],
			"time" 		=> $row[1],
			"remaining" => $row[4]
		);
	}
	return $shuttles;
}


if($method === "GET"){
	lookup($inviteCode, $zipCode);
}

if($method === "POST"){
	$task = (isset($argv[2]) && is_numeric($argv[2]) && strlen($argv[2]) > 0) ? $argv[2] : null;
	// echo("Task: ". $task."\n");
	if($task){
		$taskFile = sprintf("%s/../public_html/tasks/%d.json", __DIR__, $task);
		if($_task = json_decode(file_get_contents($taskFile), true /*assoc*/)){
			// echo("Invite code: ". $_task["invite_code"]."\n");
			confirm($_task);
		} else {
			echo("Couldn't read or decode task file.");
		}
	}
}

// if($method === "POST"){
// 	$attending 			= "N";//(isset($_POST["attending"]) && is_bool($_POST["attending"]) ? $_POST["attending"] : null);
// 	$number_attending 	= 50;//(isset($_POST["size"]) && strlen(trim($_POST["size"])) > 0 ? trim($_POST["size"]) : null);

// 	if(isset($inviteCode) && is_string($inviteCode) && strlen($inviteCode) > 0){
// 		foreach ($values as $i => $row) {
// 			if (isset($row[10]) && $row[10] == $inviteCode){
// 				// echo("Row: ". $i."\n");
// 				$r = $i+1; //true row number
// 				$writeRange = "Guestlist!L".$r.":N".$r;
// 				// echo("What's the write range?  ". $writeRange);
// 				// exit();
// 				$values = [
// 				    [$attending, $number_attending, date('Y-m-d H:i:s')] //Yes or No, size of party, time replied
// 				];
// 				$body = new Google_Service_Sheets_ValueRange([
// 				  'values' => $values
// 				]);
// 				$params = [
// 				  'valueInputOption' => "USER_ENTERED"
// 				];
// 				$result = $service->spreadsheets_values->update($spreadsheetId, $writeRange,
// 				    $body, $params);
// 				printf("%d cells updated.", $result->getUpdatedCells());
// 			}
// 		}
// 	}
// }
?>