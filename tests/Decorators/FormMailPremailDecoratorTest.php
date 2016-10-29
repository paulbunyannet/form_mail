<?php
namespace Pbc\FormMail\Tests\Decorators;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Pbc\FormMail\Decorators\FormMailPremailDecorator;

/**
 * ${CLASS_NAME}
 *
 * Created 10/4/16 12:51 PM
 * Description of this file here....
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package ${NAMESPACE}
 * @subpackage Subpackage
 */

class FormMailPremailDecoratorTest extends \TestCase
{

    use DatabaseTransactions;
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
     * @test
     * @group decorators
     */
    public function setUpPremailerWithVariables()
    {
        $premailerMock = \Mockery::mock('\\Pbc\\Premailer');
        $premailer = new FormMailPremailDecorator($premailerMock, ['a' => 'b']);
        $this->assertSame('b', $premailer->a);
    }
}
