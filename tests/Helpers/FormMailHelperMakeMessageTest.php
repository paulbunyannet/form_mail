<?php
/**
 * FormMailHelperMakeMessageTest
 *
 * Created 5/9/16 5:20 PM
 * Test for FormMailHelper::MakeMessageTest
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Tests\Http\Controllers
 * @subpackage Subpackage
 */

namespace Tests\Helpers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\FormMailHelper;
use Tests\TestCase;

/**
 * Class FormMailHelperTest
 * @package Tests\Http\Controllers
 */
class FormMailHelperMakeMessageTest extends TestCase
{
    use DatabaseTransactions;
    /**
     * @var
     */
    protected $configFile;
    protected $data = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'name' => $this->faker->name,
            'head' => $this->faker->paragraph,
            'subject' => $this->faker->sentence,
            'body' => $this->faker->sentence,
            'recipient' => $this->faker->email,
            'sender' => $this->faker->email,
            'branding' => $this->faker->sentence,
            'form' => __FUNCTION__,
            'resource' => __METHOD__,
        ];
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetSubject()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertStringContainsString($this->data['subject'], $message->subject['recipient']);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetHead()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertStringContainsString($this->data['body'], $message->head['recipient']);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetRecipient()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertSame($this->data['recipient'], $message->recipient);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetSender()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertSame($this->data['sender'], $message->sender);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetBranding()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertSame($this->data['branding'], $message->branding);
    }


    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetForm()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertSame($this->data['form'], $message->form);
    }


    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetResource()
    {
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertSame($this->data['resource'], $message->resource);
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToRecipientWhenQueued()
    {
        $this->updateConfigForQueueAndConfirmation(true, true);
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertStringContainsString($this->data['subject'], $message->message_to_recipient['subject']);
        $this->assertSame($this->data['recipient'], $message->message_to_recipient['recipient']);
        $this->assertSame($this->data['sender'], $message->message_to_recipient['sender']);
        $this->assertSame($this->data['branding'], $message->message_to_recipient['branding']);
        $this->assertStringContainsString($this->data['body'], $message->message_to_recipient['head']);
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

        $this->data['premailer'] = $premailerMock;
        $premailerMock->shouldReceive(  'html')->andReturn([
            'resource' => $this->data['resource'],
            'head' => $this->data['head'],
            'subject' => $this->data['subject'],
            'recipient' => $this->data['recipient'],
            'sender' => $this->data['sender'],
            'branding' => $this->data['branding'],
            'html' => '<html><head><body>' . $this->data['head'] . '</body></head></html>',
            'text' => $this->data['head']
        ]);
        $this->data['premailer'] = $premailerMock;

        $message = \FormMailHelper::makeMessage($this->data);
        // once message is passed though premailer then the
        // return array should have keys "html" and "text"
        $this->assertArrayHasKey('html', $message->message_to_recipient);
        $this->assertStringContainsString($this->data['head'], $message->message_to_recipient['html']);
        $this->assertArrayHasKey('text', $message->message_to_recipient);
        // in addition the return array should
        // have the original field passed
        // to makeMessage
        $this->assertStringContainsString($this->data['head'], $message->message_to_recipient['text']);
        $this->assertSame($this->data['subject'], $message->message_to_recipient['subject']);
        $this->assertSame($this->data['recipient'], $message->message_to_recipient['recipient']);
        $this->assertSame($this->data['sender'], $message->message_to_recipient['sender']);
        $this->assertSame($this->data['branding'], $message->message_to_recipient['branding']);
        $this->assertSame($this->data['head'], $message->message_to_recipient['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageSetsMessageToSenderWhenQueued()
    {
        $this->updateConfigForQueueAndConfirmation(true, true);
        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertStringContainsString($this->data['recipient'], $message->message_to_sender['subject']);
        $this->assertStringContainsString($this->data['name'], $message->message_to_sender['subject']);
        $this->assertSame($this->data['recipient'], $message->message_to_sender['recipient']);
        $this->assertSame($this->data['sender'], $message->message_to_sender['sender']);
        $this->assertSame($this->data['branding'], $message->message_to_sender['branding']);
        $this->assertStringContainsString($this->data['body'], $message->message_to_sender['head']);
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
        $this->data['name'] = 'Jacey O\'Keefe';

        $message = \FormMailHelper::makeMessage($this->data);
        $this->assertStringContainsString($this->data['recipient'], $message->message_to_sender['subject']);
        $this->assertStringContainsString($this->data['name'], $message->message_to_sender['subject']);
        $this->assertSame($this->data['recipient'], $message->message_to_sender['recipient']);
        $this->assertSame($this->data['sender'], $message->message_to_sender['sender']);
        $this->assertSame($this->data['branding'], $message->message_to_sender['branding']);
        $this->assertStringContainsString($this->data['body'], $message->message_to_sender['head']);
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

        $this->data['premailer'] = $premailerMock;
        $premailerMock->shouldReceive('html')->andReturn([
            'resource' => $this->data['resource'],
            'head' => $this->data['head'],
            'subject' => $this->data['subject'],
            'recipient' => $this->data['recipient'],
            'sender' => $this->data['sender'],
            'branding' => $this->data['branding'],
            'html' => '<html><head><body>' . $this->data['head'] . '</body></head></html>',
            'text' => $this->data['head']
        ]);
        $this->data['premailer'] = $premailerMock;

        $message = \FormMailHelper::makeMessage($this->data);
        // once message is passed though premailer then the
        // return array should have keys "html" and "text"
        $this->assertArrayHasKey('html', $message->message_to_sender);
        $this->assertStringContainsString($this->data['head'], $message->message_to_sender['html']);
        $this->assertArrayHasKey('text', $message->message_to_sender);
        // in addition the return array should
        // have the original field passed
        // to makeMessage
        $this->assertStringContainsString($this->data['head'], $message->message_to_sender['text']);
        $this->assertSame($this->data['subject'], $message->message_to_sender['subject']);
        $this->assertSame($this->data['recipient'], $message->message_to_sender['recipient']);
        $this->assertSame($this->data['sender'], $message->message_to_sender['sender']);
        $this->assertSame($this->data['branding'], $message->message_to_sender['branding']);
        $this->assertSame($this->data['head'], $message->message_to_sender['head']);
        $this->resetOriginalConfigForQueueAndConfirmation();
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageWillThrowExceptionWhenWritingToTheDB()
    {
        $this->expectException(\Exception::class);
        $mock = \Mockery::mock('\\Pbc\\FormMail\\FormMailHelper[create]');
        $mock->shouldReceive('create')->once()->andThrow(\Exception::class);
        $this->app->instance(FormMail::class, $mock);
        $this->app->make(FormMailHelper::class)->makeMessage();
    }

    /**
     * @test
     * @group MakeMessage
     */
    public function testMakeMessageWillThrowExceptionWhenWritingMessageToRecipient()
    {
        $this->expectException(\Exception::class);
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
     */
    public function testMakeMessageWillThrowExceptionWhenWritingMessageToSender()
    {
        $this->expectException(\Exception::class);
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
