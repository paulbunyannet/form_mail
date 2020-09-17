<?php
namespace Tests;

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
class FormMailTest extends TestCase
{
    use DatabaseTransactions;

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
    /**
     * Check that the subject column can be set
     * @test
     * @group model
     */
    public function the_subject_value_in_the_database_will_be_set()
    {
        $subject = ['sender' => $this->faker->sentence, 'recipient' => $this->faker->sentence];
        $form = new FormMail(['subject' => $subject]);
        $this->assertSame($form->subject, $subject);

    }
}
