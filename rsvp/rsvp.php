<?php
date_default_timezone_set('America/Los_Angeles');
header('Content-type:application/json;charset=utf-8');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../rsvp-config.php';
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

define('APPLICATION_NAME', 'Wedding RSVP');
define('CREDENTIALS_PATH', __DIR__ . '/.credentials/weddingRSVP.json');
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
// $spreadsheetId = $SPREADSHEET_ID["production"];
function isProduction($bool){
	global $SPREADSHEET_ID;
	return isset($bool) && is_bool($bool) && $bool === true ? $SPREADSHEET_ID["production"] : $SPREADSHEET_ID["test"];
}

// $range = 'Guestlist!A:T';
// $response = $service->spreadsheets_values->get($spreadsheetId, $range);
// $values = $response->getValues();

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


function confirm($options){
	global $service;
	// decide what sheet we're using
	$sheet_id = isset($options["testing"]) && is_bool($options["testing"]) && $options["testing"] === true ? isProduction(false) : isProduction(true);

	if(isset($options["attending"]) && is_string($options["attending"]) /* && isset($options["num_attending"]) && is_numeric($options["num_attending"])*/){
		if(isset($options["invite_code"]) && is_string($options["invite_code"]) && strlen($options["invite_code"]) > 0){
			$values = lookup($options);
			if(isset($values) && is_array($values) && count($values) > 0){
				foreach ($values as $i => $row) {
					if (isset($row[11]) && $row[11] == $options["invite_code"]){
						// echo("Row: ". $i."\n");
						$r = $i+1; //true row number (accounting for header row)
						$writeRange = "Guestlist!E".$r.":U".$r;
						// echo("What's the write range?  ". $writeRange);
						$guest_coming = isset($options["attending"]) && $options["attending"] == "Y";
						$vals = null;
						if($guest_coming){
							$vals = [[
								isset($options["email"]) && strlen($options["email"]) > 0 ? $options["email"] : Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								$options["attending"], 
							    $options["num_attending"],
							    date('Y-m-d H:i:s'), 
							    stripcslashes($options["notes"]), //Yes or No, size of party, time replied, notes
							    $options["attending_welcome"],
							    $options["shuttle"],
							    Google_Model::NULL_VALUE,
							    Google_Model::NULL_VALUE,
							    $options["attending_brunch"]
							]];
						} else { //guest not attending
							$vals = [[
								isset($options["email"]) && strlen($options["email"]) > 0 ? $options["email"] : Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								Google_Model::NULL_VALUE,
								"N", //$options["attending"], 
							    0, //$options["num_attending"],
							    date('Y-m-d H:i:s'), 
							    stripcslashes($options["notes"]), //Yes or No, size of party, time replied, notes
							    "N", //$options["attending_welcome"],
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
						// echo("attending is: ". $options["attending"]);
						// echo("num_attending is: ". $options["num_attending"]);
						// exit();
						try {
							$result = $service->spreadsheets_values->update($sheet_id, $writeRange, $body, $params);	
						} catch (Exception $e) {
				    		echo 'Values not updated. Error: ', $e;
						}
						// echo($result->updatedData);
						// printf("%d cells updated.", $result->getUpdatedCells());
						$output = array(
							"status" 			=> "SUCCESS",
							"user"				=> array(
								"invite_code"	=> $row[11]
							),
							"responseHeadline" 	=> "Thank you! Your RSVP has been recorded.",
							"responseText" 		=> ($guest_coming ? "Looking forward to seeing you up in Paso!" : "We will miss your presence!")
						);
						echo json_encode($output);
						return $output;
						sendEmail($options);
					}
				}
			} else {
				echo("No values found from spreadsheet");
			}
		}
	} else {
		echo("Either attending or num_attending is not formatted correctly");
	}
	// $attending 			= "N";//(isset($_POST["attending"]) && is_bool($_POST["attending"]) ? $_POST["attending"] : null);
	// $num_attending 		= 50;//(isset($_POST["size"]) && strlen(trim($_POST["size"])) > 0 ? trim($_POST["size"]) : null);
}

function lookup($options){
	global $service;
	// decide what sheet we're using
	$sheet_id = isset($options["testing"]) && is_bool($options["testing"]) && $options["testing"] === true ? isProduction(false) : isProduction(true);
	
	// Get the API client and construct the service object.
	$client = getClient();
	// $service = new Google_Service_Sheets($client);
	// Get the values from the spreadsheet
	// $spreadsheetId = $SPREADSHEET_ID;
	$range = 'Guestlist!A:U';
	try{
		$response = $service->spreadsheets_values->get($sheet_id, $range);
	} catch(Exception $e){
		echo("Caught exception: ". $e->getMessage()."\n");
	}
		
	if(isset($response["values"]) && is_array($response["values"]) && count($response["values"]) > 0){
		return $response->getValues();
	}
	return false;
}

function lookupGuest($options){
	// decide what sheet we're using
	$sheet_id = isset($options["testing"]) && is_bool($options["testing"]) && $options["testing"] === true ? isProduction(false) : isProduction(true);
	
	// global $spreadsheetId;
	// Get the API client and construct the service object.
	// $client = getClient();
	// $service = new Google_Service_Sheets($client);
	// Get the values from the spreadsheet
	// $spreadsheetId = $SPREADSHEET_ID;
	// $range = 'Guestlist!A:T';
	// $response = $service->spreadsheets_values->get($sheet_id, $range);
	// $values = $response->getValues();

	if(isset($options["invite_code"]) && is_string($options["invite_code"]) && strlen($options["invite_code"]) > 0){
		// $service = initSheet();
		if((isset($options["zip_code"]) && is_string($options["zip_code"]) && strlen($options["zip_code"]) > 0) || (isset($options["bypass_zip"]) && $options["bypass_zip"] == true)){
			$values = lookup(array(
				"testing" 		=> isset($options["testing"]) ? $options["testing"] : false,
				"invite_code" 	=> $options["invite_code"],
				"zip_code" 		=> $options["zip_code"]
			));
			if (count($values) == 0) {
		  		print "No data found.\n";
			} else {
				$shuttles = lookupShuttles($sheet_id);
				foreach ($values as $row) {
					if ((isset($row[11]) && $row[11] == $options["invite_code"]) ){
						// printf("%s has the code %s\n", $row[0], $inviteCode);
						// print_r($row);
						// echo("YAY");
						if((isset($options["zip_code"]) && isset($row[9]) && substr($row[9], 0, 5) == substr($options["zip_code"], 0, 5)) || isset($options["bypass_zip"]) && $options["bypass_zip"] == true){
							$output = array(
								"status" => "SUCCESS",
								"shuttles" => $shuttles,
								"record" => array(
									"name" 	 			=> $row[0],
									"size" 	 			=> $row[1],
									"code" 	 			=> $row[11],
									"email"				=> $row[4],
									"attending" 		=> isset($row[12]) ? $row[12] : null,
									"num_attending" 	=> isset($row[13]) ? $row[13] : null,
									"attending_welcome" => isset($row[16]) ? $row[16] : null,
									"attending_brunch"  => isset($row[20]) ? $row[20] : null,
									"shuttle"			=> isset($row[17]) ? $row[17] : null,
									"notes"				=> isset($row[15]) ? $row[15] : null
								)
							);
							return json_encode($output);
							exit();
						}
					} 
				}
			}
		}
		
	}
	$output = array(
		"status"	=> "ERROR",
		"message"	=> "No guest(s) found under that invite code/zip code combination. Please check spelling and try again."
	);
	return json_encode($output);
}

function lookupShuttles($spreadsheetId){
	global $service;
	$shuttles = array();
	$shuttle_response = $service->spreadsheets_values->get($spreadsheetId, "Guestlist!AC6:AG7");
	foreach ($shuttle_response as $row) {
		$shuttles[] = array(
			"number" 	=> $row[0],
			"time" 		=> $row[1],
			"remaining" => $row[4]
		);
	}
	return $shuttles;
}

function sendEmail($task){
	global $EMAIL_USER, $EMAIL_PW, $EMAIL_SMTP, $EMAIL_BCC;
	if(isset($task["email"]) && is_string($task["email"]) && strlen($task["email"]) > 0){
		$response = json_decode(lookup(array(
		   	"invite_code" => $task["invite_code"], 
		   	"bypass_zip" => true
		   )), true);
		if(isset($response) && is_array($response) && isset($response["record"]) && is_array($response["record"]) && count($response["record"]) > 0){
			try {
			   $r = $response["record"];
			   $attending = isset($r["attending"]) && $r["attending"] == "Y" ? true : false;
			   $shuttle_time = "N/A";
			   foreach ($response["shuttles"] as $shuttle) {
			   		if($shuttle["number"] === $r["shuttle"]){
			   			$shuttle_time = $shuttle["time"];
			   		}
			   }
			   // echo("record is\n");
			   // print_r($r);
			   // exit();
			    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
			    //Server settings
			    // $mail->SMTPDebug = 2;                                 // Enable verbose debug output
			    $mail->isSMTP();                                      // Set mailer to use SMTP
			    $mail->Host = $EMAIL_SMTP;                            // Specify main and backup SMTP servers
			    $mail->SMTPAuth = true;                               // Enable SMTP authentication
			    $mail->Username = $EMAIL_USER;                        // SMTP username
			    $mail->Password = $EMAIL_PW;                           // SMTP password
			    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			    $mail->Port = 587;                                    // TCP port to connect to

			    //Recipients
			    $mail->setFrom($EMAIL_USER, 'Levine HQ');
			    // $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
			    
			    $mail->addAddress($r["email"] /*, name*/);               // Name is optional
			    $mail->addReplyTo('noreply@levinelavidaloca.com', 'No Reply');
				// $mail->addCC('cc@example.com');
				if(isset($EMAIL_BCC) && is_array($EMAIL_BCC) && count($EMAIL_BCC) > 0){
					foreach ($EMAIL_BCC as $bcc) {
						$mail->addBCC($bcc);
					}
				}

			    //Attachments
			    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
			    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

			    //Content
			    $mail->isHTML(true);                                  // Set email format to HTML
			    $mail->Subject = 'Danielle & Eric Wedding Confirmation - '.$r["name"];
			    
			    $body =  $r["name"].",<br/><br/>Thank you for your RSVP! ";
			    if($attending){
			    	$body .= sprintf("We're excited to celebrate with you. Here is a summary of your selections for your records:<br><br><table><tr><td><b>Attending</b></td><td>%s</td></tr><tr><td><b># Attending</b></td><td>%s</td></tr><tr><td><b>Attending Welcome Reception?</b></td><td>%s</td></tr><tr><td><b>Shuttle Time</b></td><td>%s</td></tr><tr><td><b>Attending Brunch?</b></td><td>%s</td></tr><tr><td><b>Notes</b></td><td>%s</td></tr></table>", 
				    	($attending === true ? "Yes" : "No"),
				    	$r["num_attending"],
				    	(isset($r["attending_welcome"]) && $r["attending_welcome"] === "Y" ? "Yes" : "No"),
				    	$shuttle_time,
				    	(isset($r["attending_brunch"]) && $r["attending_brunch"] === "Y" ? "Yes" : "No"),
				    	$r["notes"]
				    );
			    } else {
			    	$body .= sprintf("We're sorry you're unable to make it. You will be missed! Here is a summary of your selections for your records:<br><br><table><tr><td><b>Attending</b></td><td>%s</td></tr><tr><td><b>Notes</b></td><td>%s</td></tr></table>", 
				    	($attending === true ? "Yes" : "No"),
				    	$r["notes"]
				    );
			    }
			    $body .= "<br/>";
			    $body .= ($attending ? "Looking forward," : "Love,");
			    $body .= "<br/><br/>Danielle & Eric";

			    $mail->Body = $body;
			    // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			    $mail->send();
			    // echo 'Message has been sent';
			} catch (Exception $e) {
			    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
			}
		} else {
			echo("No lookup response!");
		}
	}
}

if($method === "GET"){
	echo lookupGuest(array(
		"invite_code"	=> $inviteCode,
		"zip_code"		=> $zipCode
	));
}

if($method === "POST"){
	$task = (isset($argv[2]) && is_numeric($argv[2]) && strlen($argv[2]) > 0) ? $argv[2] : null;
	// echo("Task: ". $task."\n");
	if($task){
		$taskFile = sprintf("%s/../public_html/tasks/%d.json", __DIR__, $task);
		if($_task = json_decode(file_get_contents($taskFile), true /*assoc*/)){
			// echo("Invite code: ". $_task["invite_code"]."\n");
			$output = confirm($_task);
			if(isset($output["status"]) && $output["status"] === "SUCCESS"){
				// sendEmail($_task);
			}
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