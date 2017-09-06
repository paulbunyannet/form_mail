<?php
/**
 * FormMailHelperMakeMessageTest
 *
 * Created 5/9/16 5:20 PM
 * Test for FormMailHelper::MakeMessageTest
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Tests\Http\Controllers
 * @subpackage Subpackage
 */

namespace Pbc\FormMail\Tests\Helpers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\FormMailHelper;

/**
 * Class FormMailHelperTest
 * @package Pbc\FormMail\Tests\Http\Controllers
 */
class FormMailHelperMakeMessageTest extends \TestCase
{
    use DatabaseTransactions;
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
     * @param \Exception $e
     *
     * @since Method available since Release 3.4.0
     *
     * @throws \Exception
     */
    protected function onNotSuccessfulTest(\Exception $e)
    {
        throw $e;
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetSubject()
    {
        $data = ['subject' => $this->faker->sentence];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertContains($data['subject'], $message->subject['recipient']);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetHead()
    {
        $data = ['body' => $this->faker->sentence];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertContains($data['body'], $message->head['recipient']);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetRecipient()
    {
        $data = ['recipient' => $this->faker->email];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertSame($data['recipient'], $message->recipient);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetSender()
    {
        $data = ['sender' => $this->faker->email];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertSame($data['sender'], $message->sender);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetBranding()
    {
        $data = ['branding' => $this->faker->sentence];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertSame($data['branding'], $message->branding);
    }


    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetForm()
    {
        $data = ['form' => __FUNCTION__];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertSame($data['form'], $message->form);
    }


    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetResource()
    {
        $data = ['resource' => __METHOD__];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertSame($data['resource'], $message->resource);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToRecipientWhenQueued()
    {
        $this->updateConfigForQueueAndConfirmation(true, true);
        $data = [
            'resource' => __METHOD__,
            'body' => $this->faker->paragraph,
            'subject' => $this->faker->sentence,
            'recipient' => $this->faker->email,
            'sender' => $this->faker->email,
            'branding' => $this->faker->sentence
        ];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertContains($data['subject'], $message->message_to_recipient['subject']);
        $this->assertSame($data['recipient'], $message->message_to_recipient['recipient']);
        $this->assertSame($data['sender'], $message->message_to_recipient['sender']);
        $this->assertSame($data['branding'], $message->message_to_recipient['branding']);
        $this->assertContains($data['body'], $message->message_to_recipient['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToRecipientWhenNotQueued()
    {
        $this->updateConfigForQueueAndConfirmation(false, true);
        $premailerMock = \Mockery::mock('\\Pbc\\Premailer');

        $data = [
            'resource' => __METHOD__,
            'head' => $this->faker->paragraph,
            'subject' => $this->faker->sentence,
            'recipient' => $this->faker->email,
            'sender' => $this->faker->email,
            'branding' => $this->faker->sentence,
            'premailer' => $premailerMock
        ];
        $premailerMock->shouldReceive('html')->andReturn([
            'resource' => $data['resource'],
            'head' => $data['head'],
            'subject' => $data['subject'],
            'recipient' => $data['recipient'],
            'sender' => $data['sender'],
            'branding' => $data['branding'],
            'html' => '<html><head><body>' . $data['head'] . '</body></head></html>',
            'text' => $data['head']
        ]);
        $data['premailer'] = $premailerMock;

        $message = \FormMailHelper::makeMessage($data);
        // once message is passed though premailer then the
        // return array should have keys "html" and "text"
        $this->assertArrayHasKey('html', $message->message_to_recipient);
        $this->assertContains($data['head'], $message->message_to_recipient['html']);
        $this->assertArrayHasKey('text', $message->message_to_recipient);
        // in addition the return array should
        // have the original field passed
        // to makeMessage
        $this->assertContains($data['head'], $message->message_to_recipient['text']);
        $this->assertSame($data['subject'], $message->message_to_recipient['subject']);
        $this->assertSame($data['recipient'], $message->message_to_recipient['recipient']);
        $this->assertSame($data['sender'], $message->message_to_recipient['sender']);
        $this->assertSame($data['branding'], $message->message_to_recipient['branding']);
        $this->assertSame($data['head'], $message->message_to_recipient['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToSenderWhenQueued()
    {
        $this->updateConfigForQueueAndConfirmation(true, true);
        $data = [
            'resource' => __METHOD__,
            'body' => $this->faker->paragraph,
            'subject' => $this->faker->sentence,
            'recipient' => $this->faker->email,
            'name' => $this->faker->name,
            'sender' => $this->faker->email,
            'branding' => $this->faker->sentence
        ];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertContains($data['recipient'], $message->message_to_sender['subject']);
        $this->assertContains($data['name'], $message->message_to_sender['subject']);
        $this->assertSame($data['recipient'], $message->message_to_sender['recipient']);
        $this->assertSame($data['sender'], $message->message_to_sender['sender']);
        $this->assertSame($data['branding'], $message->message_to_sender['branding']);
        $this->assertContains($data['body'], $message->message_to_sender['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * Test that when making a message with the sender
     * name with a special characters in it and it's
     * queued that you see the correct return
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToSenderWhenQueuedWhereNameHasSpecialCharactersInIt()
    {
        $this->updateConfigForQueueAndConfirmation(true, true);
        $data = [
            'resource' => __METHOD__,
            'body' => $this->faker->paragraph,
            'subject' => $this->faker->sentence,
            'recipient' => $this->faker->email,
            'name' => 'Jacey O\'Keefe',
            'sender' => $this->faker->email,
            'branding' => $this->faker->sentence
        ];
        $message = \FormMailHelper::makeMessage($data);
        $this->assertContains($data['recipient'], $message->message_to_sender['subject']);
        $this->assertContains($data['name'], $message->message_to_sender['subject']);
        $this->assertSame($data['recipient'], $message->message_to_sender['recipient']);
        $this->assertSame($data['sender'], $message->message_to_sender['sender']);
        $this->assertSame($data['branding'], $message->message_to_sender['branding']);
        $this->assertContains($data['body'], $message->message_to_sender['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToSenderWhenNotQueued()
    {
        $this->updateConfigForQueueAndConfirmation(false, true);
        $premailerMock = \Mockery::mock('\\Pbc\\Premailer');

        $data = [
            'resource' => __METHOD__,
            'head' => $this->faker->paragraph,
            'subject' => $this->faker->sentence,
            'recipient' => $this->faker->email,
            'sender' => $this->faker->email,
            'branding' => $this->faker->sentence,
            'premailer' => $premailerMock
        ];
        $premailerMock->shouldReceive('html')->andReturn([
            'resource' => $data['resource'],
            'head' => $data['head'],
            'subject' => $data['subject'],
            'recipient' => $data['recipient'],
            'sender' => $data['sender'],
            'branding' => $data['branding'],
            'html' => '<html><head><body>' . $data['head'] . '</body></head></html>',
            'text' => $data['head']
        ]);
        $data['premailer'] = $premailerMock;

        $message = \FormMailHelper::makeMessage($data);
        // once message is passed though premailer then the
        // return array should have keys "html" and "text"
        $this->assertArrayHasKey('html', $message->message_to_sender);
        $this->assertContains($data['head'], $message->message_to_sender['html']);
        $this->assertArrayHasKey('text', $message->message_to_sender);
        // in addition the return array should
        // have the original field passed
        // to makeMessage
        $this->assertContains($data['head'], $message->message_to_sender['text']);
        $this->assertSame($data['subject'], $message->message_to_sender['subject']);
        $this->assertSame($data['recipient'], $message->message_to_sender['recipient']);
        $this->assertSame($data['sender'], $message->message_to_sender['sender']);
        $this->assertSame($data['branding'], $message->message_to_sender['branding']);
        $this->assertSame($data['head'], $message->message_to_sender['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * @test
     * @group MakeMessage
     * @expectedException \Exception
     */
    public function testMakeMessageWillThrowExceptionWhenWritingToTheDB()
    {
        $mock = \Mockery::mock('\\Pbc\\FormMail\\FormMailHelper[create]');
        $mock->shouldReceive('create')->once()->andThrow(\Exception::class);
        $this->app->instance(FormMail::class, $mock);
        $this->app->make(FormMailHelper::class)->makeMessage();
    }

    /**
     * @test
     * @group MakeMessage
     * @expectedException \Exception
     */
    public function testMakeMessageWillThrowExceptionWhenWritingMessageToRecipient()
    {
        $mock = \Mockery::mock(
            '\\Pbc\\FormMail\\Helpers\\FormMailHelper[messageToRecipient,messageToSender]'
        );
        $mock->shouldHaveReceived('messageToRecipient')
            ->once()
            ->andThrow(\Exception::class);
    }

    /**
     * @test
     * @group MakeMessage
     * @expectedException \Exception
     */
    public function testMakeMessageWillThrowExceptionWhenWritingMessageToSender()
    {
        $mock = \Mockery::mock(
            '\\Pbc\\FormMail\\Helpers\\FormMailHelper[messageToRecipient,messageToSender]'
        );
        $mock->shouldHaveReceived('messageToRecipient')
            ->once()
            ->andReturn(true);
        $mock->shouldHaveReceived('messageToSender')
            ->once()
            ->andThrow(\Exception::class);
    }

    private function updateConfigForQueueAndConfirmation($queue, $confirmation)
    {
        $this->config['form_mail.queue'] = config(['form_mail.queue']);
        $this->config['form_mail.confirmation'] = config(['form_mail.confirmation']);
        config(['form_mail.queue' => $queue]);
        config(['form_mail.confirmation' => $confirmation]);
    }

    /**
     * Reset configuration back to original
     */
    private function resetOriginalConfigForQueueAndConfirmation()
    {
        config(['form_mail.queue' => $this->config['form_mail.queue']]);
        config(['form_mail.confirmation' => $this->config['form_mail.confirmation']]);
    }
}
