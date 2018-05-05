<?php

use PHPUnit\Framework\TestCase;
include_once __DIR__."/../rsvp/rsvp.php";
include_once __DIR__."/../rsvp-config.php";

final class ConfirmTest extends TestCase {
	/**
     * @before
     */
    public function setupBefore(){ //clear appropriate test DB values 
    	$client 	= getClient();
    	$service 	= new Google_Service_Sheets($client);
    	$sheet_id 	= isProduction(false);
    	$range 		= "Guestlist!L2:Q";
    	$requestBody = new Google_Service_Sheets_ClearValuesRequest();
    	try{
			$response = $service->spreadsheets_values->clear($sheet_id, $range, $requestBody);
		} catch(Exception $e){
			echo("Caught exception: ". $e->getMessage()."\n");
		}
    }

	public function testConfirmNo(){
        // $this->markTestIncomplete("not yet implemented");
        $inputData = array(
			"testing" 		=> true,
			"invite_code" 	=> "BEAR",
			"zip_code" 		=> "90034",
			"attending"		=> "N",
			"notes"			=> "This is the greatest test, ever.",
		);

        $input = confirm($inputData);
        // $input = isProduction(false);
		// $this->assertEquals($output["status"], "SUCCESS");

        // (
        //     [0] => Guest(s)
        //     [1] => # Invited
        //     [2] => STD
        //     [3] => Category
        //     [4] => Email
        //     [5] => Street
        //     [6] => City
        //     [7] => State
        //     [8] => Zip
        //     [9] => Invite sent?
        //     [10] => Code
        //     [11] => Attending?
        //     [12] => # Attending
        //     [13] => Date RSVPed
        //     [14] => Guest Notes
        //     [15] => Welcome Dinner?
        //     [16] => Shuttle #
        //     [17] => Table
        //     [18] => Starbucks Name
        //     [19] => Brunch?
        // )
		$vals = lookup(array("testing" => true));
		foreach ($vals as $guest) {
			if($guest[10] === $input["invite_code"]){
				$this->assertEquals($guest[11], $inputData["attending"]);
			}
		}
		// print_r($vals);
//     //     $inviteCode = "BEAR";
//     //     $output = confirm(array(
//     //         "task_id" => 1517992104,
//     //         "invite_code" => $inviteCode,
//     //         "attending" => "N",
//     //         "num_attending" => "30",
//     //         "notes" => "Ain't no thang, dawg!"
//     //     ));
//     //     print_r($output);
//     //     // exit();
//     //     $lookup = json_decode(lookup(array("invite_code" => $inviteCode, "bypass_zip" => true), true));


//     //     unset($inviteCode);
    }
    public function testConfirmYes(){
    	$this->markTestIncomplete("not yet implemented");
        // $this->assertEquals(
        //     'user@example.com',
        //     Email::fromString('user@example.com')
        // );
    }
    public function testRsvpWithoutRequiredArguments(){
        $this->markTestIncomplete("not yet implemented");
        // $this->assertEquals(
        //     'user@example.com',
        //     Email::fromString('user@example.com')
        // );
    }
}

