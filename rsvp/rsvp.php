<?php
date_default_timezone_set('America/Los_Angeles');
header('Content-type:application/json;charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';
// require_once __DIR__ . '/../rsvp-config.php';

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
echo("GET VARS:\n");
print_r($_GET);
// Get the API client and construct the service object.
// $client = getClient();
$client = new Google_Client();
$client->setApplicationName(APPLICATION_NAME);
$client->setScopes(SCOPES);
$api_key = "";
$client->setDeveloperKey($api_key);
$client->setAccessType("offline");

$service = new Google_Service_Sheets($client);
$method = isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], array("GET", "POST")) ? $_SERVER['REQUEST_METHOD'] : "GET";
//isset($argv[1]) && is_string($argv[1]) && strlen($argv[1]) > 0 ? $argv[1] : null;

// Get the values from the spreadsheet
$spreadsheetId = "";
$range = 'Guestlist!A:M';
$response = $service->spreadsheets_values->get($spreadsheetId, $range);
$values = $response->getValues();
// exit();
// $inviteCode = $_POST['code'];
$inviteCode = (isset($argv[2]) && is_string($argv[2]) && strlen($argv[2]) > 0) ? $argv[2] : null;
if($method === "POST"){
	$attending 			= "N";//(isset($_POST["attending"]) && is_bool($_POST["attending"]) ? $_POST["attending"] : null);
	$number_attending 	= 50;//(isset($_POST["size"]) && strlen(trim($_POST["size"])) > 0 ? trim($_POST["size"]) : null);

	if(isset($inviteCode) && is_string($inviteCode) && strlen($inviteCode) > 0){
		foreach ($values as $i => $row) {
			if (isset($row[10]) && $row[10] == $inviteCode){
				// echo("Row: ". $i."\n");
				$r = $i+1; //true row number
				$writeRange = "Guestlist!L".$r.":N".$r;
				// echo("What's the write range?  ". $writeRange);
				// exit();
				$values = [
				    [$attending, $number_attending, date('Y-m-d H:i:s')] //Yes or No, size of party, time replied
				];
				$body = new Google_Service_Sheets_ValueRange([
				  'values' => $values
				]);
				$params = [
				  'valueInputOption' => "USER_ENTERED"
				];
				$result = $service->spreadsheets_values->update($spreadsheetId, $writeRange,
				    $body, $params);
				printf("%d cells updated.", $result->getUpdatedCells());
			}
		}
	}
}
// exit();
// echo($method);
// echo("Invite code being searched is ".$inviteCode."\n");
// exit();
if($method === "GET"){
	if (count($values) == 0) {
	  	print "No data found.\n";
	} else {
		foreach ($values as $row) {
			if (isset($row[10]) && $row[10] == $inviteCode){
				// printf("%s has the code %s\n", $row[0], $inviteCode);
				// echo($row[0]);
				// echo("YAY");

				$output = array(
					"status" => "SUCCESS",
					"record" => array(
						"name" 	 	=> $row[0],
						"size" 	 	=> $row[1],
						"code" 	 	=> $row[10],
						"attending" => isset($row[11]) ? $row[11] : "Awaiting Reply"
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
?>