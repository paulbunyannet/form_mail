<?php
namespace Pbc\FormMail\Tests\Jobs;

/**
 * FormMailSendMessageTest
 *
 * Created 6/23/16 8:01 AM
 * Tests for the FormMailSendMessage job class
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Jobs
 */

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Pbc\FormMail\Jobs\FormMailSendMessage;

class FormMailSendMessageTest extends \TestCase
{

    use DatabaseTransactions;

    public function setup()
    {
        parent::setUp();

    }

    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Job does nothing if message_sent_to_recipient is already true
     *
     * @test
     */
    public function the_handle_does_nothing_id_message_sent_to_recipient_is_true()
    {
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(true);
        $premailerMock = Mockery::mock('\\Pbc\\Premailer');

        $job = new FormMailSendMessage($formMailMock, $premailerMock);
        $this->assertNull($job->handle());
    }
    
    /**
     * Test that handle will throw an exception if the html and
     * text keys are missing in message_to_recipient
     *
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Missing html and/or text keys in message_to_recipient
     */
    public function the_handle_throws_an_exception_if_html_and_text_keys_are_missing()
    {
        $value = ['key1' => 'bla bla bla'];
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);
        $formMailMock->shouldReceive('setAttribute');

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_recipient')->andReturn($value);
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);
        $formMailMock->shouldReceive('save')->zeroOrMoreTimes()->andReturn(true);
        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendMessage($formMailMock,$premailerMock);
        $job->handle();
    }

    /**
     * Check that handle will throw an exception if the
     * recipient is an invalid email address
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid recipient address
     * @test
     */
    public function the_handle_will_throw_an_exception_if_recipient_is_not_an_email_address()
    {
        $faker = \Faker\Factory::create();
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);

        $formMailMock->shouldReceive('setAttribute');
        $formMailMock->shouldReceive('save');

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);

        $html = ['html' => 'bla bla bla'];
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_recipient')->andReturn($html);

        $recipient = implode($faker->words(3));
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);

        $sender = $faker->email;
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('sender')->andReturn($sender);

        $subject = $faker->sentence();
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('subject')->andReturn($subject);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendMessage($formMailMock, $premailerMock);
        $job->handle();
    }


    /**
     * Check that handle will throw an exception if the
     * recipient is an invalid email address
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid sender address
     * @test
     */
    public function the_handle_will_throw_an_exception_if_sender_is_not_an_email_address()
    {
        $faker = \Faker\Factory::create();
        $formMailmock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailmock);

        $formMailmock->shouldReceive('setAttribute');
        $formMailmock->shouldReceive('save');

        $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);

        $html = ['html' => 'bla bla bla'];
        $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_recipient')->andReturn($html);

        $recipient = $faker->email;
        $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);

        $sender = implode($faker->words(3));
        $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('sender')->andReturn($sender);

        $subject = $faker->sentence();
        $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('subject')->andReturn($subject);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendMessage($formMailmock, $premailerMock);
        $job->handle();
    }

    /**
     * Check that handle will not throw exception if:
     * 1. html key are present in message_sent_to_recipient
     * 2. recipient email address is valid
     * 3. sender email address is valid
     *
     * @test
     */
    public function the_handle_does_not_throw_an_exception_if_validations_pass()
    {
        $faker = \Faker\Factory::create();
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);

        $formMailMock->shouldReceive('setAttribute');
        $formMailMock->shouldReceive('save');

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);

        $html = ['html' => 'bla bla bla'];
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_recipient')->andReturn($html);

        $recipient = $faker->email;
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);
        $formMailMock->recipient = $recipient;

        $sender = $faker->email;
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('sender')->andReturn($sender);
        $formMailMock->sender = $sender;

        $subject = $faker->sentence();
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('subject')->andReturn($subject);
        $formMailMock->subject = $subject;

        // see http://stackoverflow.com/a/31135826 for example
        \Mail::shouldReceive('failures')->zeroOrMoreTimes()->andReturn([]);
        \Mail::shouldReceive('send')->once()->with(
            'pbc_form_mail_template::body',
            \Mockery::on(function ($data) use ($formMailMock) {
                $this->assertArrayHasKey('data', $data);
                $this->assertSame($data['data'], $formMailMock->message_to_recipient);
                return true;
            }),
            \Mockery::on(function (\Closure $closure) use ($formMailMock) {

                $mock = Mockery::mock('Illuminate\Mailer\Message');
                // mock to method
                $mock->shouldReceive('to')
                    ->once()
                    ->with($formMailMock->recipient)
                    ->andReturn($mock); //simulate the chaining
                // mock from method
                $mock->shouldReceive('from')
                    ->once()
                    ->with($formMailMock->sender)
                    ->andReturn($mock); //simulate the chaining
                // mock subject method
                $mock->shouldReceive('subject')
                    ->once()
                    ->with($formMailMock->subject);
                $closure($mock);
                return true;

            })
        );

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendMessage($formMailMock, $premailerMock);
        $this->assertNull($job->handle());
    }
    
}
