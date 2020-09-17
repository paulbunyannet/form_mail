<?php

namespace Pbc\FormMail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pbc\FormMail\FormMail;
use Pbc\Premailer as Preflight;

class FormMailPreflightMessageToRecipient
{
    use InteractsWithQueue, SerializesModels, Queueable;
    public $formMail;
    public $preflight;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FormMail $formMail, Preflight $preflight)
    {
        $this->formMail = $formMail;
        $this->preflight = $preflight;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        // if we already have the html key, then do nothing
        if (array_key_exists('html', $this->formMail->message_to_recipient)) {
            return null;
        }
        // Preflight the message if there's a body key,
        // otherwise return an exception so that the
        // job can be run again.
        if (array_key_exists('body', $this->formMail->message_to_recipient)) {
            $message = $this->preflight->html(\View::make('pbc_form_mail_template::layout')->with('data', $this->formMail->message_to_recipient)->render());
            $this->formMail->message_to_recipient = $message;
            $this->formMail->save();
        } else {
            throw new \Exception('Missing body key in message_to_recipient');
        }

    }
}
