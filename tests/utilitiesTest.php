<?php

use PHPUnit\Framework\TestCase;
include_once __DIR__."/../rsvp/rsvp.php";
include_once __DIR__."/../rsvp-config.php";

/**
 * @covers RSVP lookup get request & RSVP lookupGuest
 */
final class UtilitiesTest extends TestCase {
    public function testIsProduction(){
        global $SPREADSHEET_ID;
        $t = $SPREADSHEET_ID["test"];
        $p = $SPREADSHEET_ID["production"];

        $case1 = isProduction("blah"); //truthy str
        $this->assertEquals($case1, $t);
        // $case2 = isProduction(); //no args
        // $this->assertEquals($case2, $t);
        $case3 = isProduction(""); //empty str
        $this->assertEquals($case3, $t);
        $case4 = isProduction(true);
        $this->assertEquals($case4, $p);
    }

    // public function testLookupShuttles(){
        
    // }

    public function testShuttlesDecrementingIntegration(){
        //more of an integration test testing the shuttle remaining count has decremented by "num_attending" available seats after a confirm request.

        $sid = isProduction(false);
        $output_before = lookupShuttles($sid);
        // print_r($output_before);
        $shuttle_key;
        $remaining;
        $input = array(
            "testing"           => true,
            "invite_code"       => "BEAR",
            "email"             => "test@test.com",
            "zip_code"          => "90034",
            "attending"         => "Y",
            "num_attending"     => 1,
            "shuttle"           => 2,
            "attending_welcome" => "Y",
            "attending_brunch"  => "Y",
            "notes"             => "Oh, I'm comin alright. Hide yo kids, hide yo wife.",
        );
        foreach ($output_before as $key => $shuttle) {
            if($shuttle["number"] == $input["shuttle"]){
                $shuttle_key = $key;
                $remaining = $shuttle["remaining"];
            }
        }
        // echo("\n\n REMAINING is ".$remaining."Beforehand. \n");
        confirm($input);
        $output_after = lookupShuttles($sid);

        // echo("\n\n REMAINING AFTER is ".$output_after[$shuttle_key]["remaining"]."\n");
        if(isset($output_after)){
            $this->assertEquals(
                intval($remaining) - 1,
                intval($output_after[$shuttle_key]["remaining"])
            ); 

        }
    }    

    // public function testIsAttending(){

        // $case1 = isProduction("blah"); //truthy str
        // $this->assertEquals($case1, $t);
        // // $case2 = isProduction(); //no args
        // // $this->assertEquals($case2, $t);
        // $case3 = isProduction(""); //empty str
        // $this->assertEquals($case3, $t);
        // $case4 = isProduction(true);
        // $this->assertEquals($case4, $p);
    // }
}