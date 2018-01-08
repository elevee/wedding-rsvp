<?php
date_default_timezone_set('America/Los_Angeles');
header('Content-type:application/json;charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/rsvp-config.php';

define('APPLICATION_NAME', 'Wedding RSVP');
define('CREDENTIALS_PATH', '~/.credentials/weddingRSVP.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/sheets.googleapis.com-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Sheets::SPREADSHEETS)
));

if (php_sapi_name() != 'cli') {
  throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
// function getClient() {
//   $client = new Google_Client();
//   $client->setApplicationName(APPLICATION_NAME);
//   $client->setScopes(SCOPES);
//   $client->setAuthConfig(CLIENT_SECRET_PATH);
//   $client->setAccessType('offline');

//   // Load previously authorized credentials from a file.
//   $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
//   if (file_exists($credentialsPath)) {
//     $accessToken = json_decode(file_get_contents($credentialsPath), true);
//   } else {
//     // Request authorization from the user.
//     $authUrl = $client->createAuthUrl();
//     printf("Open the following link in your browser:\n%s\n", $authUrl);
//     print 'Enter verification code: ';
//     $authCode = trim(fgets(STDIN));

//     // Exchange authorization code for an access token.
//     $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

//     // Store the credentials to disk.
//     if(!file_exists(dirname($credentialsPath))) {
//       mkdir(dirname($credentialsPath), 0700, true);
//     }
//     file_put_contents($credentialsPath, json_encode($accessToken));
//     printf("Credentials saved to %s\n", $credentialsPath);
//   }
//   $client->setAccessToken($accessToken);

//   // Refresh the token if it's expired.
//   if ($client->isAccessTokenExpired()) {
//     $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
//     file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
//   }
//   return $client;
// }

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
// $client = getClient();

$client = new Google_Client();
$client->setApplicationName(APPLICATION_NAME);
$client->setScopes(SCOPES);
$api_key = $API_KEY;
$client->setDeveloperKey($api_key);
$client->setAccessType("offline");
$service = new Google_Service_Sheets($client);

// Get the values from the spreadsheet
$spreadsheetId = $SPREADSHEET_ID;
$range = 'Guestlist!A:O';
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

$method = isset($argv[1]) && is_string($argv[1]) && strlen($argv[1]) > 0 ? $argv[1] : null;;
//isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], array("GET", "POST")) ? $_SERVER['REQUEST_METHOD'] : "GET"
	// $inviteCode = $_POST['code'];
$inviteCode 	= (isset($argv[2]) && is_string($argv[2]) && strlen($argv[2]) > 0) ? $argv[2] : null;
$attending 		= (isset($argv[3]) && is_string($argv[3]) && strlen($argv[3]) > 0) ? $argv[3] : null;
$num_attending 	= (isset($argv[4]) && is_string($argv[4]) && strlen($argv[4]) > 0) ? $argv[4] : null;
$notes 			= (isset($argv[5]) && is_string($argv[5]) && strlen($argv[5]) > 0) ? $argv[5] : null;

function confirm($inviteCode, $attending, $num_attending, $notes){
	global $spreadsheetId, $service, $values;
	if(isset($attending) && is_string($attending) && isset($num_attending) && is_numeric($num_attending)){
		if(isset($inviteCode) && is_string($inviteCode) && strlen($inviteCode) > 0){
			// $service = $initSheet();
			foreach ($values as $i => $row) {
				if (isset($row[10]) && $row[10] == $inviteCode){
					echo("Row: ". $i."\n");
					$r = $i+1; //true row number
					$writeRange = "Guestlist!L".$r.":O".$r;
					echo("What's the write range?  ". $writeRange);
					$vals = [
					    [$attending, $num_attending, date('Y-m-d H:i:s'), "blah"] //Yes or No, size of party, time replied, notes
					];
					//(isset($notes) && is_string($notes) && strlen($notes)>0 ? $notes : null)
					$body = new Google_Service_Sheets_ValueRange([
					  'values' => $vals
					]);
					$params = [
					  'valueInputOption' => "USER_ENTERED"
					];
					$result = $service->spreadsheets_values->update($spreadsheetId, $writeRange, $body, $params);
					printf("%d cells updated.", $result->getUpdatedCells());
				}
			}
		}
	}
	// $attending 			= "N";//(isset($_POST["attending"]) && is_bool($_POST["attending"]) ? $_POST["attending"] : null);
	// $num_attending 		= 50;//(isset($_POST["size"]) && strlen(trim($_POST["size"])) > 0 ? trim($_POST["size"]) : null);
}

function lookup($invite_code){
	global $values;
	if(isset($invite_code) && is_string($invite_code) && strlen($invite_code) > 0){
		// $service = initSheet();
		if (count($values) == 0) {
		  	print "No data found.\n";
		} else {
			foreach ($values as $row) {
				if (isset($row[10]) && $row[10] == $invite_code){
					// printf("%s has the code %s\n", $row[0], $inviteCode);
					// print_r($row);
					// echo("YAY");

					$output = array(
						"status" => "SUCCESS",
						"record" => array(
							"name" 	 	=> $row[0],
							"size" 	 	=> $row[1],
							"code" 	 	=> $row[10],
							"attending" => isset($row[11]) ? $row[11] : "Awaiting Reply",
							"notes"		=> isset($row[14]) ? $row[14] : null,
						)
					);
					echo json_encode($output);
					exit();
				} 
			}
		  	$output = array(
				"status"	=> "ERROR",
				"message"	=> "No guest(s) found under that invite code. Please check spelling and try again."
			);
			echo json_encode($output);
		}
	}
}



if($method === "GET"){
	lookup($inviteCode);
}

if($method === "POST"){
	// echo("Invite code: ". $inviteCode."\n");
	// echo("Attending: ". $attending)."\n";
	// echo("Num attending: ". $num_attending)."\n";
	// echo("Notes: ". $notes)."\n";
	// echo("Calling: confirm(".$inviteCode.", ".$attending.", ".$num_attending.", ".$notes.")");
	confirm($inviteCode, $attending, $num_attending, $notes);
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