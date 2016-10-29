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
use Pbc\FormMail\Jobs\FormMailSendConfirmationMessage;

class FormMailSendConfirmationTest extends \TestCase
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
     * Job does nothing if confirmation_sent_to_sender is set to true
     * @test
     */
    public function the_handle_does_nothing_if_confirmation_sent_to_sender_is_true()
    {
        $formMail = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMail);
        $formMail->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('confirmation_sent_to_sender')->andReturn(true);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');

        $job = new FormMailSendConfirmationMessage($formMail, $premailerMock);
        $this->assertNull($job->handle());
    }

    /**
     * Job does nothing if confirmation is not set to true
     * 
     * @test
     */
    public function the_handle_does_nothing_if_confirmation_is_set_to_false()
    {
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('confirmation_sent_to_sender')->andReturn(false);
        \Config::set('form_mail.confirmation', false);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');

        $job = new FormMailSendConfirmationMessage($formMailMock, $premailerMock);
        $this->assertNull($job->handle());
    }
    
    /**
     * Test that handle will throw an exception if the html and
     * text keys are missing in message_to_sender
     *
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Missing html and/or text keys in message_to_sender
     */
    public function the_handle_throws_an_exception_if_html_and_text_keys_are_missing()
    {
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');

        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);
        $formMailMock->shouldReceive('setAttribute');
        $formMailMock->shouldReceive('save');
        $faker = \Faker\Factory::create();
        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence() ,
            'sender' => $faker->email,
            'recipient' => $faker->email,
            'fields' => [],
            'subject' => ['sender' => 'subject to sender', 'recipient' => 'subject to recipient'],
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'head' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'head' => $faker->paragraph(), 'branding' => $faker->sentence],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph()
        ];
        foreach($html as $key => $value) {
            $formMailMock->{$key} = $value;
            $formMailMock->save();
            $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn($html);
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('confirmation_sent_to_sender')->andReturn(false);

        \Config::set('form_mail.confirmation', true);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn([]);
        $job = new FormMailSendConfirmationMessage($formMailMock, $premailerMock);
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
        $formMailMock->shouldReceive('save')->zeroOrMoreTimes()->andReturn(true);

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('confirmation_sent_to_sender')->andReturn(false);
        \Config::set('form_mail.confirmation', true);
        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence() ,
            'sender' => $faker->email,
            'recipient' => $faker->sentence(),
            'fields' => [],
            'subject' => ['sender' => 'subject to sender', 'recipient' => 'subject to recipient'],
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph()
        ];
        foreach($html as $key => $value) {
            $formMailMock->{$key} = $value;
            $formMailMock->save();
            $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn($html);

        $recipient = implode($faker->words(3));
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);

        $sender = $faker->email;
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('sender')->andReturn($sender);

        $subject = $faker->sentence();
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('subject')->andReturn($subject);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendConfirmationMessage($formMailMock, $premailerMock);
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
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);

        $formMailMock->shouldReceive('setAttribute');
        $formMailMock->shouldReceive('save')->zeroOrMoreTimes()->andReturn(true);

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('confirmation_sent_to_sender')->andReturn(false);
        \Config::set('form_mail.confirmation', true);

        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence(),
            'sender' => $faker->sentence(),
            'recipient' => $faker->email,
            'fields' => [],
            'subject' => ['sender' => 'subject to sender', 'recipient' => 'subject to recipient'],
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph()
        ];
        foreach($html as $key => $value) {
            $formMailMock->{$key} = $value;
            $formMailMock->save();
            $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn($html);

        $recipient = $faker->email;
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);

        $sender = implode($faker->words(3));
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('sender')->andReturn($sender);

        $subject = $faker->sentence();
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('subject')->andReturn($subject);

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendConfirmationMessage($formMailMock, $premailerMock);
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

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('confirmation_sent_to_sender')->andReturn(false);
        \Config::set('form_mail.confirmation', true);
        $recipient = $faker->email;
        $sender = $faker->email;
        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'sender' => $sender,
            'recipient' => $recipient,
            'fields' => [],
            'subject' => ['recipient' => 'subject to recipient', 'sender' => 'subject to sender'],
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'head' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'head' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph()
        ];
        foreach($html as $key => $value) {
            $formMailMock->{$key} = $value;
            $formMailMock->save();
            $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_recipient')->andReturn($html['message_to_recipient']);
        $formMailMock->message_to_recipient = $html['message_to_recipient'];

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_sender')->andReturn($html['message_to_sender']);
        $formMailMock->message_to_sender = $html['message_to_sender'];

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);
        $formMailMock->recipient = $recipient;

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('sender')->andReturn($sender);
        $formMailMock->sender = $sender;

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('subject')->andReturn($html['subject']);
        $formMailMock->subject = $html['subject'];

        // see http://stackoverflow.com/a/31135826 for example
        \Mail::shouldReceive('failures')->zeroOrMoreTimes()->andReturn([]);
        \Mail::shouldReceive('send')->once()->with(
            'pbc_form_mail_template::body',
            \Mockery::on(function ($data) use ($formMailMock) {
                $this->assertArrayHasKey('data', $data);
                $this->assertSame($data['data'], $formMailMock->message_to_sender);
                return true;
            }),
            \Mockery::on(function (\Closure $closure) use ($formMailMock) {


                $mock = Mockery::mock('Illuminate\Mailer\Message');
                // mock to method
                $mock->shouldReceive('to')
                    ->once()
                    ->with($formMailMock->sender)
                    ->andReturn($mock); //simulate the chaining
                // mock from method
                $mock->shouldReceive('from')
                    ->once()
                    ->with($formMailMock->recipient)
                    ->andReturn($mock); //simulate the chaining
                // mock subject method
                $mock->shouldReceive('subject')
                    ->once()
                    ->with($formMailMock->message_to_sender['subject']);
                $closure($mock);
                return true;

            })
        );

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);

        $job = new FormMailSendConfirmationMessage($formMailMock, $premailerMock);
        $this->assertNull($job->handle());
    }
    
}
