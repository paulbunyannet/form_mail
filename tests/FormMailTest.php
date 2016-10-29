<?php
namespace Pbc\FormMail\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Pbc\FormMail\FormMail;

/**
 * FormMailTest
 *
 * Created 10/5/16 12:28 PM
 * Tests for Form Mail Model
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package ${NAMESPACE}
 * @subpackage Subpackage
 */
class FormMailTest extends \TestCase
{
    use DatabaseTransactions;
    public function setup()
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();

    }

    public function tearDown()
    {
        parent::tearDown();
        Mockery::close();
    }

    /**
     * Check that the head column can be set
     * @test
     * @group model
     */
    public function the_head_value_in_the_database_will_be_set()
    {
        $head = $this->faker->paragraph;
        $form = new FormMail(['head' => $head]);
        $this->assertSame($form->head, $head);

    }
}
