<?php
namespace Pbc\FormMail\Tests\Helpers;
/**
 * GreetingTest
 *
 * Created 10/30/16 11:05 AM
 * Test for greeting helper
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Helpers
 * @subpackage Subpackage
 */

namespace Pbc\FormMail\Tests\Helpers;


use Pbc\FormMail\Helpers\Greeting;

class GreetingTest extends \TestCase
{

    /**
     * @test
     * @group Greeting
     */
    public function testGreetingMakeGreetingWithDefaultGreeting()
    {
        $default = Greeting::defaultGreeting();
        $name = 'Some Guy';
        $data = ['name' => $name];

        $greeting = Greeting::makeGreeting($data);
        $m = new \Mustache_Engine();
        $this->assertSame($greeting, $m->render($default, $data));




    }
    
}
