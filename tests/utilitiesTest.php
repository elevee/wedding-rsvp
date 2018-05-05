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