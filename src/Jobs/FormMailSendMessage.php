<?php

namespace Pbc\FormMail\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pbc\FormMail\FormMail;

/**
 * Class FormMailSendMessage
 * @package Pbc\FormMail\Jobs
 */
class FormMailSendMessage extends FormMailSend implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param FormMail $formMail
     */
    public function __construct(FormMail $formMail)
    {
        parent::__construct($formMail);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->formMail->message_sent_to_recipient) {

            $this->validateMessageToRecipient();
            $this->validateRecipient();
            $this->validateSender();

            \Mail::send('pbc_form_mail_template::body', ['data' => $this->formMail->message_to_recipient],
                function ($message) {
                    $message->to($this->formMail->recipient)
                        ->from($this->formMail->sender)
                        ->subject($this->formMail->subject);
                });

            $this->formMail->message_sent_to_recipient = 1;
            $this->formMail->save();
        }
    }
}
