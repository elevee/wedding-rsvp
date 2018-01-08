<?php

use PHPUnit\Framework\TestCase;
include_once __DIR__."/../rsvp/rsvp.php";
/**
 * @covers RSVP lookup get request
 */
final class LookupTest extends TestCase {
    public function testSampleLookup(){
        $output = json_decode(lookup("BEAR"), true);
        // print_r($output);
        $this->assertEquals(
	        $output["record"]["name"],
	        "Jeff Hendler"
        );
        $this->assertEquals(
	        $output["record"]["size"],
	        1
        );
    }

    // public function testInvalidInviteCode(){
        
        // $this->expectException(InvalidArgumentException::class);

        // Email::fromString('invalid');
    // }
}

final class ConfirmTest extends TestCase {
	public function testRsvpNo(){
        $inviteCode = "BEAR";
        $output = confirm(
        	$inviteCode,
        	"N",
        	40,
        	"I'm so alone. Say hello to your motha fo me, mmk?"
        	);
        echo($output);
        exit();
        $lookup = json_decode(lookup($inviteCode), true);

        $this->assertEquals(
        	$lookup["record"]["attending"],
        	"Not Attending"
        );
        unset($inviteCode);
    }
    // public function testRsvpYes(){
        // $this->assertEquals(
        //     'user@example.com',
        //     Email::fromString('user@example.com')
        // );
    // }
    // public function testRsvpWithoutRequiredArguments(){
        // $this->assertEquals(
        //     'user@example.com',
        //     Email::fromString('user@example.com')
        // );
    // }
}

?>