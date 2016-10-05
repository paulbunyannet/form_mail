<?php
/**
 * FormMailHelperTest
 *
 * Created 5/9/16 5:20 PM
 * Test for FormMailHelper
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Http\Controllers
 * @subpackage Subpackage
 */

namespace Pbc\FormMail\Tests\Helpers;

use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\FormMail\Http\Controllers\FormMailController;

/**
 * Class FormMailHelperTest
 * @package Pbc\FormMail\Tests\Http\Controllers
 */
class FormMailHelperTest extends \TestCase
{
    /**
     * @var \Faker\Factory
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
        $this->faker = \Faker\Factory::create();
        parent::setUp();
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
     * Check that if branding is set in the config
     * it returns the right string.
     *
     * @test
     * @group helpers
     */
    public function nameBrandingWillReturnAStringIfBrandingConfigIsSet()
    {
        $helper = new FormMailHelper();
        $originalBranding = \Config::get('form_mail.branding');
        $branding = implode('-', $this->faker->words(10));
        // set branding string
        \Config::set('form_mail.branding', $branding);
        $this->assertSame($branding, $helper->makeBranding());
        // reset branding
        \Config::set('form_mail.branding', $originalBranding);
    }

    /**
     * Check if a branding like was already set,
     * and if it was it's returning what we expect.
     *
     * @test
     * @group helpers
     */
    public function brandingWillReturnStringThatIsAlreadySet()
    {
        $helper = new FormMailHelper();
        $branding = implode('-', $this->faker->words(3));
        $data = ['branding' => $branding];
        $setBranding = $helper->branding($data);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $setBranding);
        $this->assertSame($branding, $data['branding']);
    }

    /**
     * Check to see if the formName
     * returns in the correct format
     *
     * @test
     * @group helpers
     */
    public function makeFormNameWillReturnAString()
    {
        $helper = new FormMailHelper();
        $route = implode('-', $this->faker->words(3));
        \Route::shouldReceive('currentRouteName')->once()->andReturn($route);
        $formName = $helper->makeFormName();
        $this->isTrue(is_string($formName));
        $this->assertSame($formName, preg_replace('/[\s+\-]/', '_', $route));
    }

    /**
     * Test to make sure that the formName
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function formNameWillReturnStringThatIsAlreadySet()
    {
        $helper = new FormMailHelper();
        $route = implode('-', $this->faker->words(3));
        $data = ['formName' => $route];
        $formName = $helper->formName($data);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $formName);
        $this->assertSame($route, $data['formName']);
    }

        /**
     * Check to see if the formName
     * returns in the correct format
     *
     * @test
     * @group helpers
     */
    public function makeSubjectWillReturnAString()
    {
        $helper = new FormMailHelper();
        $formName = $this->faker->word;
        $subject = $helper->makeSubject(['formName' => $formName]);
        $this->assertSame($subject, ucfirst($formName).' Form Submission');
    }

    /**
     * Test to make sure that the formName
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function subjectWillReturnStringThatIsAlreadySet()
    {
        $helper = new FormMailHelper();
        $subject = implode('-', $this->faker->words(3));
        $data = ['subject' => $subject];
        $formName = $helper->subject($data);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $formName);
        $this->assertSame($subject, $data['subject']);
    }

    /**
     * Check to see if the recipient
     * returns in the correct format
     *
     * @test
     * @group helpers
     */
    public function makeRecipientWillReturnAString()
    {
        $helper = new FormMailHelper();
        $url = $this->faker->url;
        $form = implode('_', $this->faker->words(3));
        \Config::shouldReceive('get')->once()->with('app.url')->andReturn($url);
        $recipientName = $helper->makeRecipient($form);
        $this->isTrue(is_string($recipientName));
        $this->assertSame($recipientName, $form . '@' . str_replace_first('www.', '', parse_url($url, PHP_URL_HOST)));
    }

    /**
     * Test to make sure that the recipient
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function recipientWillReturnStringThatIsAlreadySet()
    {
        $helper = new FormMailHelper();
        $email = $this->faker->email;
        $form = implode('_', $this->faker->words(3));
        $data = ['recipient' => $email];
        $recipient = $helper->recipient($data, $form);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $recipient);
        $this->assertSame($email, $data['recipient']);
    }

    /**
     * Check to see if the resource
     * returns in the correct format
     *
     * @test
     * @group helpers
     */
    public function makeResourceWillReturnAString()
    {
        $helper = new FormMailHelper();
        $class = implode('\\', $this->faker->words(3));
        $function = $this->faker->word;
        $resource = $helper->makeResource($class, $function);
        $this->isTrue(is_string($resource));
        $this->assertSame($resource, str_replace('\\', '.', strtolower($class)) . '.' . strtolower($function));
    }

    /**
     * Test to make sure that the recipient
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function resourceWillReturnStringThatIsAlreadySet()
    {
        $helper = new FormMailHelper();
        $class = implode('\\', $this->faker->words(3));
        $function = $this->faker->word;
        $data = ['resource' => $class];
        $resource = $helper->resource($data, $class, $function);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $resource);
        $this->assertSame($class, $data['resource']);
    }

    /**
     * Check to see if the resource
     * returns in the correct format
     *
     * @test
     * @group helpers
     */
    public function makeFieldsWillReturnAnArray()
    {
        $helper = new FormMailHelper();
        $input = [
            'something' => 'fo bar bazz',
            'something-label' => 'Foo Bar Bazz',
            'fields' => ['something']
        ];
        $request = \Mockery::mock();
        $request->shouldReceive('input')->once()->with('fields')->andReturn($input['fields']);
        $request->shouldReceive('input')->twice()->with('something-label')->andReturn($input['something-label']);
        $request->shouldReceive('input')->twice()->with('something')->andReturn($input['something']);
        $fields = $helper->makeFields($request);
        $this->isTrue(is_array($fields));
        $this->assertSame(
            $fields,
            [
                ['label' => $input['something-label'], 'value' => $input['something'], 'field' => 'something']
            ]
        );
    }

    /**
     * Test to make sure that the recipient
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function fieldsWillReturnArrayThatIsAlreadySet()
    {
        $helper = new FormMailHelper();
        $request = null;
        $input = [[
            'value' => 'fo bar bazz',
            'label' => 'Foo Bar Bazz',
            'field' => 'field'
        ]];
        $data = array(
            'fields' => $input
        );
        $fields = $helper->fields($data, $request);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $fields);
        $this->assertSame($input, $data['fields']);
    }


    /**
     * Test to make sure that the head method
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function head_will_not_set_different_value_if_already_exists()
    {
        $helper = new FormMailHelper();
        $class = implode('\\', $this->faker->words(3));
        $data = ['head' => $class];
        $resource = $helper->head($data);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $resource);
        $this->assertSame($class, $data['head']);
    }

    /**
     * Test to make sure that the sender method
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function sender_will_not_set_different_value_if_already_exists()
    {
        $helper = new FormMailHelper();
        $class = implode('\\', $this->faker->words(3));
        $data = ['sender' => $class];
        $resource = $helper->sender($data);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $resource);
        $this->assertSame($class, $data['sender']);
    }

    /**
     * Test to make sure that the response method
     * will use existing if already set.
     *
     * @test
     * @group helpers
     */
    public function response_will_not_set_different_value_if_already_exists()
    {
        $helper = new FormMailHelper();
        $class = implode('\\', $this->faker->words(3));
        $data = ['response' => $class];
        $resource = $helper->response($data);
        $this->assertInstanceOf('\\Pbc\\FormMail\\Helpers\\FormMailHelper', $resource);
        $this->assertSame($class, $data['response']);
    }

    /**
     * Test to make sure that the recipient method
     * will use existing config if already set.
     *
     * @test
     * @group helpers
     */
    public function recipient_will_be_pulled_from_config_if_key_exists()
    {
        $helper = new FormMailHelper();
        $email = $this->faker->email();
        $form = $this->faker->word;
        config(['form_mail.recipient.'.$form => $email]);
        $data = [];

        $helper->recipient($data, $form);
        $this->assertSame($data[FormMailController::RECIPIENT], $email);
    }
    
}
