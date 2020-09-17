<?php
namespace Tests\Jobs;

/**
 * FormMailSendMessageTest
 *
 * Created 6/23/16 8:01 AM
 * Tests for the FormMailSendMessage job class
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Tests\Jobs
 */

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\Confirmation;
use Pbc\FormMail\Helpers\Queue;
use Pbc\FormMail\Jobs\FormMailSendConfirmationMessage;
use Tests\TestCase;

class FormMailSendConfirmationTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Job does nothing if confirmation_sent_to_sender is set to true
     * @test
     */
    public function the_handle_does_nothing_if_confirmation_sent_to_sender_is_true()
    {
        $formMail = new \Pbc\FormMail\FormMail();
        $formMail->confirmation_sent_to_sender = true;
        $formMail->confirmation = Confirmation::getDefault();

        $this->app->instance('Pbc\FormMail\FormMail', $formMail);

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
        $formMail = new FormMail();
        $formMail->confirmation_sent_to_sender = false;
        $formMail->confirmation = false;

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');

        $job = new FormMailSendConfirmationMessage($formMail, $premailerMock);
        $this->assertNull($job->handle());
    }

    /**
     * Test that handle will throw an exception if the html and
     * text keys are missing in message_to_sender
     *
     * @test
     */
    public function the_handle_throws_an_exception_if_html_and_text_keys_are_missing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing html and/or text keys in message_to_sender');

        $formMail = new FormMail();

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
            'confirmation' => true,
            'queue' => Queue::getDefault(),
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'head' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'head' => $faker->paragraph(), 'branding' => $faker->sentence],
        ];
        foreach($html as $key => $value) {
            $formMail->{$key} = $value;
        }

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn([]);
        $job = new FormMailSendConfirmationMessage($formMail, $premailerMock);
        $job->handle();
    }

    /**
     * Check that handle will throw an exception if the
     * recipient is an invalid email address
     *
     * @test
     */
    public function the_handle_will_throw_an_exception_if_recipient_is_not_an_email_address()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid recipient address');

        $faker = \Faker\Factory::create();
        $formMail = new FormMail();

        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence() ,
            'sender' => $faker->email,
            'recipient' => $faker->sentence(),
            'fields' => [],
            'queue' => Queue::getDefault(),
            'confirmation' => true,
            'subject' => ['sender' => 'subject to sender', 'recipient' => 'subject to recipient'],
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
        ];
        foreach($html as $key => $value) {
            $formMail->{$key} = $value;
          }

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);

        $job = new FormMailSendConfirmationMessage($formMail, $premailerMock);
        $job->handle();
    }


    /**
     * Check that handle will throw an exception if the
     * recipient is an invalid email address
     *
     * @test
     * @group isolation
     */
    public function the_handle_will_throw_an_exception_if_sender_is_not_an_email_address()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid sender address');

        $faker = \Faker\Factory::create();
        $formMail = new FormMail();

        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence(),
            'sender' => $faker->sentence(),
            'recipient' => $faker->email,
            'fields' => [],
            'subject' => ['sender' => 'subject to sender', 'recipient' => 'subject to recipient'],
            'branding' => '',
            'confirmation' => true,
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
        ];
        foreach($html as $key => $value) {
            $formMail->{$key} = $value;
        }

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->zeroOrMoreTimes()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendConfirmationMessage($formMail, $premailerMock);
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
        $formMail = new FormMail();

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
            'confirmation' => true,
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'head' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'head' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
        ];
        foreach($html as $key => $value) {
            $formMail->{$key} = $value;
        }

        // see http://stackoverflow.com/a/31135826 for example
        \Mail::shouldReceive('failures')->zeroOrMoreTimes()->andReturn([]);
        \Mail::shouldReceive('send')->once()->with(
            'pbc_form_mail_template::body',
            \Mockery::on(function ($data) use ($formMail) {
                $this->assertArrayHasKey('data', $data);
                $this->assertSame($data['data'], $formMail->message_to_sender);
                return true;
            }),
            \Mockery::on(function (\Closure $closure) use ($formMail) {


                $mock = Mockery::mock('Illuminate\Mailer\Message');
                // mock to method
                $mock->shouldReceive('to')
                    ->once()
                    ->with($formMail->sender)
                    ->andReturn($mock); //simulate the chaining
                // mock from method
                $mock->shouldReceive('from')
                    ->once()
                    ->with($formMail->recipient)
                    ->andReturn($mock); //simulate the chaining
                // mock subject method
                $mock->shouldReceive('subject')
                    ->once()
                    ->with($formMail->message_to_sender['subject']);
                $closure($mock);
                return true;

            })
        );

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);

        $job = new FormMailSendConfirmationMessage($formMail, $premailerMock);
        $this->assertNull($job->handle());
    }

}
