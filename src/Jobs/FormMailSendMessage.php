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
class FormMailSendMessage extends FormMailJob implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @param FormMail $formMail
     * @param \Pbc\Premailer $premailer
     */
    public function __construct(FormMail $formMail, \Pbc\Premailer $premailer)
    {
        parent::__construct($formMail, $premailer);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->formMail->message_sent_to_recipient) {

            // preflight the message to inline the css
            $this->preflight('message_to_recipient')
            // do validations
                 ->validateMessageToRecipient()
                 ->validateRecipient()
                 ->validateSender();

            \Mail::send('pbc_form_mail_template::body', ['data' => $this->formMail->message_to_recipient],
                function ($message) {
                    $message->to($this->formMail->recipient)
                        ->from($this->formMail->sender)
                        ->subject($this->formMail->subject);
                });

            $this->validateMailSent();

            $this->formMail->message_sent_to_recipient = 1;
            $this->formMail->save();
        }
    }

}
