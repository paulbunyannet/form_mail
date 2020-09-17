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
use Pbc\FormMail\Jobs\FormMailJob;
use Pbc\FormMail\Jobs\FormMailSendMessage;
use Tests\TestCase;

class FormMailJobTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * Check that when calling the premailer method on FormMailJob
     * with message_to_sender that it still returns an array with
     * html and text keys
     * @test
     *
     */
    public function the_premailer_method_still_returns_html_and_text_on_message_to_sender_even_if_premailer_returns_an_exception()
    {
        $faker = \Faker\Factory::create();
        $premailerMock = Mockery::Mock('\\Pbc\\Premailer');
        $formMailmock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailmock);
        $formMailmock->shouldReceive('setAttribute');
        $formMailmock->shouldReceive('save');

        $html = [
            'message_to_sender' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
        ];

        foreach($html as $key => $value) {
            $formMailmock->{$key} = $value;
            $formMailmock->save();
            $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }
        $premailerMock->shouldReceive('html')->andThrow('\\Exception');
        $job = new FormMailJob($formMailmock, $premailerMock);

        $job->preflight('message_to_sender');
        $this->assertArrayHasKey('html', $job->formMail->message_to_sender);
        $this->assertArrayHasKey('text', $job->formMail->message_to_sender);
        $this->assertStringContainsString($html['message_to_sender']['body'], $job->preflighted['message_to_sender']['html'], "html contains body content");
        $this->assertStringContainsString($html['message_to_sender']['subject'], $job->preflighted['message_to_sender']['html'], "html contains subject content");
        $this->assertStringContainsString($html['message_to_sender']['branding'], $job->preflighted['message_to_sender']['html'], "html contains branding content");

    }
    /**
     * Check that when calling the premailer method on FormMailJob
     * with message_to_recipient that it still returns an array with
     * html and text keys
     * @test
     *
     */
    public function the_premailer_method_still_returns_html_and_text_on_message_to_recipient_even_if_premailer_returns_an_exception()
    {
        $faker = \Faker\Factory::create();
        $premailerMock = Mockery::Mock('\\Pbc\\Premailer');
        $formMailmock = Mockery::mock('Pbc\FormMail\FormMail');
        $this->app->instance('Pbc\FormMail\FormMail', $formMailmock);
        $formMailmock->shouldReceive('setAttribute');
        $formMailmock->shouldReceive('save');

        $html = [
            'message_to_recipient' => ['html' => $faker->paragraph(), 'text' => $faker->paragraph(), 'subject' => $faker->sentence(), 'body' => $faker->paragraph(), 'branding' => $faker->sentence],
        ];

        foreach($html as $key => $value) {
            $formMailmock->{$key} = $value;
            $formMailmock->save();
            $formMailmock->shouldReceive('getAttribute')->zeroOrMoreTimes()->with($key)->andReturn($value);
        }
        $premailerMock->shouldReceive('html')->andThrow('\\Exception');
        $job = new FormMailJob($formMailmock, $premailerMock);

        $job->preflight('message_to_recipient');
        $this->assertArrayHasKey('html', $job->formMail->message_to_recipient);
        $this->assertArrayHasKey('text', $job->formMail->message_to_recipient);
        $this->assertStringContainsString($html['message_to_recipient']['body'], $job->preflighted['message_to_recipient']['html'], "html contains body content");
        $this->assertStringContainsString($html['message_to_recipient']['subject'], $job->preflighted['message_to_recipient']['html'], "html contains subject content");
        $this->assertStringContainsString($html['message_to_recipient']['branding'], $job->preflighted['message_to_recipient']['html'], "html contains branding content");

    }
}
