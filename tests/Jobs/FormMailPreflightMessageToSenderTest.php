<?php
/**
 * FormMailPreflightMessageToRecipientTest
 *
 * Created 6/23/16 10:29 AM
 * Tests for the FormMailPreflightMessageToRecipient Job class
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Jobs
 * @subpackage Subpackage
 */

namespace Pbc\FormMail\Tests\Jobs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Pbc\FormMail\Jobs\FormMailPreflightMessageToSender;


/**
 * Class FormMailPreflightMessageToRecipientTest
 * @package Pbc\FormMail\Tests\Jobs
 */
class FormMailPreflightMessageToSenderTest extends \TestCase
{

    use DatabaseTransactions;
    /**
     *
     */
    public function setup()
    {
        parent::setUp();
    }

    /**
     *
     */
    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Test to make sure if message_to_recipient does not contain html or body that an exception is thrown
     *
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Missing body key in message_to_sender
     */
    public function handle_throws_an_exception_if_there_is_no_body_key_in_message_to_sender()
    {
        $formMail = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMail);
        $formMail->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn(['key1' => 'bla bla bla']);
        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $job = new FormMailPreflightMessageToSender($formMail, $premailerMock);
        $job->handle();
    }

    /**
     * Check that handle does nothing if there's already a html key
     *
     * @test
     */
    public function handle_does_nothing_if_there_is_already_a_html_key_in_message_to_sender()
    {
        $formMail = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMail);
        $formMail->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn(['html' => 'bla bla bla']);
        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $job = new FormMailPreflightMessageToSender($formMail, $premailerMock);
        $this->assertNull($job->handle());
    }

    /**
     * test that the handler does not return an exception
     *
     * @test
     */
    public function handle_returns_null_when_body_exists()
    {
        $formMail = Mockery::mock('Pbc\FormMail\FormMail');
        $formMail->shouldReceive('save');
        $formMail->shouldReceive('setAttribute');
        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        
        $this->app->instance('Pbc\FormMail\FormMail', $formMail);
        $formMail->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn(['body' => 'bla bla bla', 'subject' => 'subject of message', 'branding' => 'bla bla bla']);
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);
        $job = new FormMailPreflightMessageToSender($formMail, $premailerMock);
        
        $this->assertNull($job->handle());

    }
}
