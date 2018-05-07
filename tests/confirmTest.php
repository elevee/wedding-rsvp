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
    	$ranges		= array("Guestlist!E2:E", "Guestlist!L2:Q");
    	$requestBody = new Google_Service_Sheets_BatchClearValuesRequest(array("ranges" => $ranges));
    	try{
			$response = $service->spreadsheets_values->batchClear($sheet_id, $requestBody);
		} catch(Exception $e){
			echo("Caught exception: ". $e->getMessage()."\n");
		}
    }

	public function testConfirmNo(){
        $input = array(
			"testing" 			=> true,
			"invite_code" 		=> "BEAR",
			"email"				=> "test@test.com",
			"zip_code" 			=> "90034",
			"attending"			=> "N",
			// "attending_welcome" => "N",
			// "attending_brunch"  => "N",
			"notes"			=> "This is the greatest test, ever.",
		);

        $output = confirm($input);
        //verify rersponse output
		$this->assertEquals($output["status"], "SUCCESS");
		$this->assertEquals($output["responseText"], "We will miss your presence!");

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
		//verify changes in test DB
		$vals = lookup(array("testing" => true));
		foreach ($vals as $guest) {
			if(isset($guest[10]) && $guest[10] === $input["invite_code"]){
				$this->assertEquals($guest[11], $input["attending"]);
				$this->assertEquals($guest[4], $input["email"]);
				$this->assertEquals($guest[12], 0); //num attending
				$this->assertEquals($guest[14], $input["notes"]);
				$this->assertEquals($guest[15], "N"); //attending welcome
				$this->assertEquals($guest[19], "N"); //attending brunch
			}
		}
    }
    public function testConfirmYes(){
    	// $this->markTestIncomplete("not yet implemented");
    	$input = array(
			"testing" 			=> true,
			"invite_code" 		=> "BEAR",
			"email"				=> "test@test.com",
			"zip_code" 			=> "90034",
			"attending"			=> "Y",
			"num_attending"		=> 1,
			"shuttle"			=> 2,
			"attending_welcome" => "Y",
			"attending_brunch"  => "Y",
			"notes"				=> "Oh, I'm comin alright. Hide yo kids, hide yo wife.",
		);

        $output = confirm($input);
        //verify rersponse output
		$this->assertEquals($output["status"], "SUCCESS");
		$this->assertEquals($output["responseText"], "Looking forward to seeing you up in Paso!");

		//verify changes in test DB
		$vals = lookup(array("testing" => true));
		foreach ($vals as $guest) {
			if(isset($guest[10]) && $guest[10] === $input["invite_code"]){
				$this->assertEquals($guest[11], $input["attending"]);
				$this->assertEquals($guest[4], $input["email"]);
				$this->assertEquals($guest[12], $input["num_attending"]); //num attending
				$this->assertEquals($guest[16], $input["shuttle"]);
				$this->assertEquals($guest[15], $input["attending_welcome"]);
				$this->assertEquals($guest[19], $input["attending_brunch"]);
				$this->assertEquals($guest[14], $input["notes"]);
			}
		}
    }
    public function testRsvpWithoutRequiredArguments(){
        $this->markTestIncomplete("not yet implemented");
    }
}

