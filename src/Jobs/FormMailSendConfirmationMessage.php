<?php

namespace Pbc\FormMail\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\Premailer;
use Pbc\Bandolier\Type\Strings;

/**
 * Class FormMailSendConfirmationMessage
 * @package Pbc\FormMail\Jobs
 */
class FormMailSendConfirmationMessage extends FormMailSend implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var FormMail
     */
    public $formMail;

    public $doConfirmation;

    /**
     * Create a new job instance.
     *
     * @param FormMail $formMail
     * @param \Pbc\Premailer $premailer
     */
    public function __construct(FormMail $formMail, Premailer $premailer)
    {
        parent::__construct($formMail, $premailer);
        $this->doConfirmation = \Config::get('form_mail.confirmation');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->formMail->confirmation_sent_to_sender && $this->doConfirmation) {

            // preflight the message to the sender (confirmation message)
            $this->preflight('message_to_sender')
            // do validations
                 ->validateMessageToSender()
                 ->validateRecipient()
                 ->validateSender();
            
            \Mail::send('pbc_form_mail_template::body', ['data' => $this->formMail->message_to_sender], function ($message) {
                $message->to($this->formMail->sender)
                    ->from($this->formMail->recipient)
                    ->subject($this->formMail->subject);
            });

            $this->formMail->confirmation_sent_to_sender = 1;
            $this->formMail->save();
        }
    }


}
