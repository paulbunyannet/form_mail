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
use Mockery;

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
    public function MakeFormNameWillReturnAString()
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
        $data['formName'] = $route;
        $helper->formName($data);
        $this->assertSame($route, $data['formName']);
    }
}
