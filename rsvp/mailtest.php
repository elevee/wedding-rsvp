<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/../rsvp-config.php';

// $provider = new League\OAuth2\Client\Provider\Google([
//     'clientId'     => $MAIL_CLIENT_ID,
//     'clientSecret' => $MAIL_CLIENT_SECRET,
//     // 'redirectUri'  => 'https://example.com/callback-url',
//     'hostedDomain' => 'https://example.com',
//     'accessType'   => 'offline',
// ]);

// if (!empty($_GET['error'])) {

//     // Got an error, probably user denied access
//     exit('Got error: ' . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'));

// } elseif (empty($_GET['code'])) {

//     // If we don't have an authorization code then get one
//     $authUrl = $provider->getAuthorizationUrl();
//     $_SESSION['oauth2state'] = $provider->getState();
//     header('Location: ' . $authUrl);
//     exit;

// } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

//     // State is invalid, possible CSRF attack in progress
//     unset($_SESSION['oauth2state']);
//     exit('Invalid state');

// } else {

//     // Try to get an access token (using the authorization code grant)
//     $token = $provider->getAccessToken('authorization_code', [
//         'code' => $_GET['code']
//     ]);

//     // Optional: Now you have a token you can look up a users profile data
//     try {

//         // We got an access token, let's now get the owner details
//         $ownerDetails = $provider->getResourceOwner($token);

//         // Use these details to create a new profile
//         printf('Hello %s!', $ownerDetails->getFirstName());

//     } catch (Exception $e) {

//         // Failed to get user details
//         exit('Something went wrong: ' . $e->getMessage());

//     }

//     // Use this to interact with an API on the users behalf
//     echo $token->getToken();

//     // Use this to get a new access token if the old one expires
//     echo $token->getRefreshToken();

//     // Number of seconds until the access token will expire, and need refreshing
//     echo $token->getExpires();
// }

$mail = new PHPMailer(true);                              // Passing `true` enables exceptions
try {
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
    $mail->addAddress('test@test.com');               // Name is optional
    // $mail->addReplyTo($REPLY_TO, 'No Reply');
    // $mail->addCC('cc@example.com');
	if(isset($EMAIL_BCC) && is_array($EMAIL_BCC) && count($EMAIL_BCC) > 0){
		foreach ($EMAIL_BCC as $address) {
	    	$mail->addBCC($address);
	    }
	}

    //Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Confirmation';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}