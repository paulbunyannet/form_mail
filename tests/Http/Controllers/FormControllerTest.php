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
use \Mockery;


class FormControllerTest extends \TestCase
{
    use WithoutMiddleware;

    protected $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
    }

    public function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
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
            'fields' => ['field1','field2','email','name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('success', $decode);
        $this->assertContains('Thanks for filling out the Form Mail.Send form,  we will get back to you as soon as possible!', $decode->success[0]);
        $this->assertContains($parameters['email'], $decode->success[0]);
        $this->assertContains(htmlentities($parameters['name']), $decode->success[0]);
        $this->assertContains($parameters['field1'], $decode->success[0]);
        $this->assertContains($parameters['field2'], $decode->success[0]);
    }
    /**
     * @test
     */
    public function it_can_submit_form_mail_with_geo_location()
    {
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1','field2','email','name'],
            'geo_lat' => $this->faker->latitude,
            'geo_long' => $this->faker->longitude,
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
    public function it_can_submit_but_throws_an_error_when_sending_message()
    {
        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1','field2','email','name'],

        ];
        \Mail::shouldReceive('send')->once()->withAnyArgs()->andThrowExceptions([new \Exception()]);
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('error', $decode);
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
            'fields' => ['field1','field2','email','name']
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
            'fields' => ['field1','field2','email','name']
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
            'fields' => ['field1','field2','email','name']
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
    public function it_will_use_text_branding_if_none_is_set()
    {
        // temp remove the custom config for the form mail controller
        $configFile = $this->app->basePath() . '/config/form_mail.php';
        copy($configFile, $configFile.'.tmp');
        file_put_contents($configFile, '<?php return ["branding" => ""];');

        $parameters = [
            'email' => $this->faker->email,
            'name' => $this->faker->name,
            'field1' => $this->faker->paragraph,
            'field2' => $this->faker->paragraph,
            'fields' => ['field1','field2','email','name']
        ];
        $response = $this->call('POST', 'form-mail/send', $parameters);
        $this->assertJson($response->getContent());
        $this->assertResponseOk();
        $decode = json_decode($response->getContent());
        $this->assertObjectHasAttribute('success', $decode);

        // add the file back
        unlink($configFile);
        copy($configFile.'.tmp', $configFile);
        unlink($configFile.'.tmp');

    }

    /**
     * @test
     */
    public function it_uses_a_generated_string_instead_of_branding()
    {

        \Config::shouldReceive('get')->once()->withArgs(['form_mail.branding'])->andReturn(false);
        $url = $this->faker->url;
        \Config::shouldReceive('get')->once()->withArgs(['app.url'])->andReturn($url);
        $helper = new \Pbc\FormMail\Helpers\FormMailHelper;
        $data = ['formName' => 'form'];
        $helper->branding($data);
        $this->assertSame($data['branding'], $url.' Form Form');
        
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
}
