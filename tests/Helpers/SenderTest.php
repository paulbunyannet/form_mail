<?php
/**
 * SenderTest
 *
 * Created 10/30/16 11:51 AM
 * Tests for Sender helper
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Helpers
 */

namespace Pbc\FormMail\Tests\Helpers;


use Pbc\FormMail\Helpers\Sender;

class SenderTest extends \TestCase
{

    /**
     * @test
     * @group Sender
     */
    public function testSenderUsesAddressSubmittedToMakeSender()
    {
        $sender = 'example@example.com';
        $this->assertSame($sender, Sender::makeSender(['sender' => $sender]));
    }
    /**
     * @test
     * @group Sender
     */
    public function testSenderUsesRequestAddressToMakeSender()
    {
        $sender = 'example@example.com';
        \Request::replace(['email' => $sender]);
        $this->assertSame($sender, Sender::makeSender([]));
    }
    /**
     * @test
     * @group Sender
     */
    public function testSenderUsesSenderAndUrlToCreateAddress()
    {
        $sender = 'example';
        $url = 'http://www.example.com';
        $this->assertSame($sender . '@' . str_replace_first('www.', '', parse_url($url, PHP_URL_HOST)), Sender::makeSender([], $sender, $url));
    }
    /**
     * @test
     * @group Sender
     */
    public function testSenderUsesSenderAndConfigUrlToCreateAddress()
    {
        $sender = 'example';
        $url = \Config::get('app.url');
        $this->assertSame($sender . '@' . str_replace_first('www.', '', parse_url($url, PHP_URL_HOST)), Sender::makeSender([], $sender));
    }
}
