<?php
namespace Tests\Decorators;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Pbc\FormMail\Decorators\FormMailPremailDecorator;
use Tests\TestCase;

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

class FormMailPremailDecoratorTest extends TestCase
{

    use DatabaseTransactions;

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
