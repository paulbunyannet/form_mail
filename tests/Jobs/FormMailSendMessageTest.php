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
use Pbc\FormMail\Jobs\FormMailSendMessage;
use Tests\TestCase;

class FormMailSendMessageTest extends TestCase
{

    use DatabaseTransactions;

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
     */
    public function the_handle_throws_an_exception_if_html_and_text_keys_are_missing()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing html and/or text keys in message_to_recipient');
        $faker = \Faker\Factory::create();
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);
        $formMailMock->shouldReceive('setAttribute');

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);
        $formMailMock->shouldReceive('save')->zeroOrMoreTimes()->andReturn(true);

        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence() ,
            'sender' => $faker->email,
            'recipient' => $faker->sentence(),
            'fields' => [],
            'subject' => 'Subject',
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph()
        ];
        foreach($html as $key => $value) {
            $formMailMock->{$key} = $value;
            $formMailMock->save();
            $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }

        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->andReturn(['html' => 'bla bla html', 'text' => 'bla bla bla text']);


        $job = new FormMailSendMessage($formMailMock,$premailerMock);
        $job->handle();
    }

    /**
     * Check that handle will throw an exception if the
     * recipient is an invalid email address
     * @test
     */
    public function the_handle_will_throw_an_exception_if_recipient_is_not_an_email_address()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid recipient address');
        $faker = \Faker\Factory::create();
        $formMailMock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailMock);

        $formMailMock->shouldReceive('setAttribute');
        $formMailMock->shouldReceive('save');

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);

        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence() ,
            'sender' => $faker->email,
            'recipient' => $faker->sentence(),
            'fields' => [],
            'subject' => 'Subject',
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
     * @test
     */
    public function the_handle_will_throw_an_exception_if_sender_is_not_an_email_address()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid sender address');
        $faker = \Faker\Factory::create();
        $formMailmock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailmock);

        $formMailmock->shouldReceive('setAttribute');
        $formMailmock->shouldReceive('save');

        $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_sent_to_recipient')->andReturn(false);

        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => $faker->sentence() ,
            'sender' => $faker->sentence(),
            'recipient' => $faker->email,
            'fields' => [],
            'subject' => 'Subject',
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph()
        ];

        foreach($html as $key => $value) {
            $formMailmock->{$key} = $value;
            $formMailmock->save();
            $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }


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
        $recipient = $faker->email;
        $sender = $faker->email;
        $html = [
            'resource' => 'bla_bla_bla',
            'form' => 'some_form',
            'subject' => ['sender' => 'sender subject', 'recipient' => 'recipient subject'] ,
            'sender' => $sender,
            'recipient' => $recipient,
            'fields' => [],
            'branding' => '',
            'confirmation_sent_to_sender' => false,
            'message_sent_to_recipient' => false,
            'message_to_sender' => ['subject' => 'sender subject', 'html' => $faker->paragraph(), 'text' => $faker->paragraph()],
            'message_to_recipient' => ['subject' => 'recipient subject', 'html' => $faker->paragraph(), 'text' => $faker->paragraph()],
            'html' => $faker->paragraph(),
            'text' => $faker->paragraph(),
        ];
        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('message_to_recipient')->andReturn($html);

        $formMailMock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with('recipient')->andReturn($recipient);
        $formMailMock->recipient = $recipient;

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
                    ->with($formMailMock->message_to_recipient['subject']);
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
