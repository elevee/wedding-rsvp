<?php

use PHPUnit\Framework\TestCase;
include_once __DIR__."/../rsvp/rsvp.php";
include_once __DIR__."/../rsvp-config.php";

/**
 * @covers RSVP lookup get request & RSVP lookupGuest
 */
final class LookupTest extends TestCase {
    public function testLookup(){
        $output = lookup(array("testing" => true));
        // print_r($output);
        $this->assertEquals((count($output) > 0), true);
    }

    public function testGuestLookup(){
        $record = lookupGuest(array(
            "testing"     => true,
            "invite_code" => "BEAR",
            "zip_code" => "90034"
        ));
        // print_r(json_decode($record, true));
        $r = json_decode($record, true);
        $this->assertEquals($r["record"]["name"], "Guesty McGuestface");
        $this->assertEquals($r["record"]["size"], 1);
    }

    public function testShuttleLookup(){
        global $SPREADSHEET_ID;
        $record = lookupShuttles($SPREADSHEET_ID["test"]);
        // print_r($record);
        // $r = json_decode($record, true);
        $this->assertEquals(count($record) > 0, 1);
        $this->assertEquals($record[1]["time"], "5:15 PM");
    }

    public function testErroneousCodeLookup(){
        $output = lookupGuest(array(
            "testing"       => true,
            "invite_code"   => "COWBOY",
            // "bypass_zip"    => true
        ));
        // print_r($output);
        $o = json_decode($output, true);
        $this->assertEquals($o["status"], "ERROR");
        // $this->expectException(Google_Service_Exception::class);
    }

    public function testErroneousZipLookup(){
        $output = lookupGuest(array(
            "testing"       => true,
            "invite_code"   => "BEAR", //does exist
            "zip_code"      => "90210" //wrong zip
        ));
        // echo("\n\n");
        // print_r($output);
        $o = json_decode($output, true);
        $this->assertEquals($o["status"], "ERROR");
    }
}

// class ExceptionTest extends TestCase {
//     
// }