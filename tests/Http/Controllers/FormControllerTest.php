<?php
/**
 * FormControllerTest
 *
 * Created 5/9/16 5:20 PM
 * Test for Form controller
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Http\Controllers
 * @subpackage Subpackage
 */

namespace Pbc\FormMail\Tests\Http\Controllers;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;

/**
 * Class FormControllerTest
 * @package Pbc\FormMail\Tests\Http\Controllers
 */
class FormControllerTest extends \TestCase
{
    use WithoutMiddleware;

    /**
     * @var
     */
    protected $faker;
    /**
     * @var
     */
    protected $configFile;

    /**
     * Set up test
     */
    public function setUp()
    {
        parent::setUp();
        exec('php artisan migrate:refresh');
        $this->faker = \Faker\Factory::create();
        $this->configFile = config_path('form_mail.php');
    }

    /**
     * Tear down test
     */
    public function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    /**
     * This method is called when a test method did not execute successfully.
     *
     * @param Exception $e
     *
     * @since Method available since Release 3.4.0
     *
     * @throws Exception
     */
    protected function onNotSuccessfulTest($e)
    {
        throw $e;
    }

    /**
     * Tests to see if queue is turned on and confirmation is not that
     * there will be a record in jobs for FormMailSendMessage but
     * not FormMailSendConfirmationMessage
     * @test
     */
    public function it_will_queue_to_recipient_but_not_queue_confirmation()
    {
        $this->updateConfigForQueueAndConformation(true, false);
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $this->call('POST', 'form-mail/send', $parameters);

        $sender = \DB::table('jobs')->where('payload', 'like', '%FormMailSendMessage%');
        $this->assertSame($sender->count(), 1);

        $confirmer = \DB::table('jobs')->where('payload', 'like', '%FormMailSendConfirmationMessage%');
        $this->assertSame($confirmer->count(), 0);
        
        $this->resetOriginalConfiguration();

    }

    /**
     * Tests to see if queue is turned on and confirmation is set that
     * there will be a record in jobs for FormMailSendMessage and
     * FormMailSendConfirmationMessage.
     * @test
     */
    public function it_will_queue_to_recipient_and_queue_confirmation()
    {
        $this->updateConfigForQueueAndConformation(true, true);

        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $this->call('POST', 'form-mail/send', $parameters);

        $sender = \DB::table('jobs')->where('payload', 'like', '%FormMailSendMessage%');
        $this->assertSame($sender->count(), 1);

        $confirmer = \DB::table('jobs')->where('payload', 'like', '%FormMailSendConfirmationMessage%');
        $this->assertSame($confirmer->count(), 1);

        $this->resetOriginalConfiguration();

    }


    /**
     * Tests to see if queue is turned off and confirmation is not that
     * there will not be a record in jobs for FormMailSendMessage or
     * FormMailSendConfirmationMessage. There will be a record
     * in the form_mail db for this message that will be
     * marked as sent, but no confirmation will be sent
     * @test
     */
    public function it_will_send_right_away_but_not_send_confirmation()
    {
        $this->updateConfigForQueueAndConformation(false, false);

        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $this->call('POST', 'form-mail/send', $parameters);

        $this->resetOriginalConfiguration();

        $sender = \DB::table('jobs')->where('payload', 'like', '%FormMailSendMessage%');
        $this->assertSame($sender->count(), 0);

        $confirmer = \DB::table('jobs')->where('payload', 'like', '%FormMailSendConfirmationMessage%');
        $this->assertSame($confirmer->count(), 0);

        $sent = \DB::table('form_mail')->where(
            [
                [ 'sender', '=', $parameters['email'] ],
                [ 'message_sent_to_recipient', '=', 1 ],
                [ 'confirmation_sent_to_sender', '=', '']
            ]
        );
        $this->assertSame($sent->count(), 1);


    }

    /**
     * @test
     */
    public function it_can_submit_form_mail()
    {
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('success', $decode);
        $this->assertContains(
            'Thanks for filling out the Form Mail.Send form,  we will get back to you as soon as possible!',
            $decode->success[0]
        );
        $this->assertContains($parameters['email'], $decode->success[0]);
        $this->assertContains(htmlspecialchars($parameters['name'], ENT_QUOTES), $decode->success[0]);
        $this->assertContains($parameters['field1'], $decode->success[0]);
        $this->assertContains($parameters['field2'], $decode->success[0]);
    }

    /**
     * @test
     */
    public function it_can_submit_but_throws_an_error_when_sending_message()
    {
        $this->updateConfigForQueueAndConformation(false, false);
        
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name'],

        ];
        \Mail::shouldReceive('send')->once()->withAnyArgs()->andThrowExceptions([new \Exception()]);
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        
        $this->resetOriginalConfiguration();
    }


    /**
     * @test
     */
    public function it_fails_if_name_field_is_missing()
    {
        $parameters = [
            'email' => $this->faker->email,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        $this->assertSame(['The name field is required.'], $decode->error);
    }


    /**
     * @test
     */
    public function it_fails_if_fields_field_is_missing()
    {
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        $this->assertSame(['The fields field is required.'], $decode->error);
    }

    /**
     * @test
     */
    public function it_fails_if_fields_field_is_not_an_array()
    {
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => $this->faker->sentence
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        $this->assertSame(['The fields must be an array.'], $decode->error);
    }


    /**
     * @test
     */
    public function it_fails_to_send_if_email_is_missing()
    {
        $parameters = [
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        $this->assertSame(['The email field is required.'], $decode->error);
    }

    /**
     * @test
     */
    public function it_fails_to_send_if_email_is_invalid()
    {
        $parameters = [
            'email' => $this->faker->sentence,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        $this->assertSame(['The email must be a valid email address.'], $decode->error);
    }

    /**
     * @test
     */
    public function it_fails_if_custom_rules_do_not_pass()
    {
        $rule = implode('_', $this->faker->words());
        $original = \Config::get('form_mail.rules');
        \Config::set('form_mail.rules', [$rule => 'required']);
        $parameters = [];

        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
        $this->assertSame(['The ' . str_replace('_', ' ', $rule) . ' field is required.'], $decode->error);
        \Config::set('form_mail.rules', $original);


    }


    /**
     * @test
     */
    public function it_will_use_text_branding_if_none_is_set()
    {
        // temp remove the custom config for the form mail controller
        //$this->setConfigFile('<?php return ["branding" => ""];');

        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1', 'field2', 'email', 'name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('success', $decode);

    }

    /**
     * @test
     */
    public function it_uses_a_generated_string_instead_of_branding()
    {
        $originalBranding = \Config::get('form_mail.branding');
        $originalUrl = \Config::get('app.url');

        \Config::set('form_mail.branding', false);

        $url = $this->faker->url;
        \Config::set('app.url', $url);

        $helper = new \Pbc\FormMail\Helpers\FormMailHelper;
        $data = ['formName' => 'form'];
        $helper->branding($data);
        $this->assertSame($data['branding'], $url . ' Form Form');

        \Config::set('form_mail.branding', $originalBranding);
        \Config::set('app.url', $originalUrl);

    }

    /**
     * @test
     */
    public function it_uses_a_provided_branding()
    {
        $originalBranding = \Config::get('form_mail.branding');
        $branding = '<img src="http://placehold.it/350x150">';
        \Config::set('form_mail.branding', $branding);

        $helper = new \Pbc\FormMail\Helpers\FormMailHelper;
        $data = ['formName' => 'form'];
        $helper->branding($data);
        $this->assertSame($data['branding'], $branding);

        \Config::set('form_mail.branding', $originalBranding);

    }

    /**
     * @test
     */
    public function premailer_will_still_return_html_if_api_fails()
    {
        $data = [
            'subject' => $this->faker->sentence,
            'branding' => $this->faker->company,
            'body' => $this->faker->paragraph,
            'footer' => $this->faker->sentence,
        ];
        $premailerMock = Mockery::mock('\\Pbc\\Premailer');
        $premailerMock->shouldReceive('html')->once()->withAnyArgs()->andThrowExceptions([new \Exception]);
        $helper = new \Pbc\FormMail\Helpers\FormMailHelper;
        $message = $helper->premailer($premailerMock, $data);

        $this->assertArrayHasKey('html', $message);
        $this->assertArrayHasKey('text', $message);

        $this->assertContains($data['subject'], $message['html']);
        $this->assertContains($data['branding'], $message['html']);
        $this->assertContains($data['body'], $message['html']);
        $this->assertContains($data['footer'], $message['html']);
    }

    /**
     * Update configuration with queue and confirmation status
     *
     * @param $queue
     * @param $confirmation
     */
    private function updateConfigForQueueAndConformation($queue, $confirmation)
    {
        config(['form_mail.queue' => $queue]);
        config(['form_mail.confirmation' => $confirmation]);
    }

    /**
     * Reset configuration back to original
     */
    private function resetOriginalConfiguration()
    {
        config(['form_mail.queue' => true]);
        config(['form_mail.confirmation' => false]);
    }
}
