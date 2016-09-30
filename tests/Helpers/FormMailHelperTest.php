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
}
