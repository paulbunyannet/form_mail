<?php

namespace Pbc\FormMail\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pbc\FormMail\FormMail;
use Pbc\Premailer as Preflight;

class FormMailPreflightMessageToSender
{
    use InteractsWithQueue, SerializesModels, Queueable;
    public FormMail $formMail;
    public Preflight $premailer;

    /**
     * FormMailPreflightMessageToSender constructor.
     * @param FormMail $formMail
     * @param Preflight $premailer
     */
    public function __construct(FormMail $formMail, Preflight $premailer)
    {
        $this->formMail = $formMail;
        $this->premailer = $premailer;
    }

    /**
     * Execute the job.
     *
     * @throws \Exception
     */
    public function handle()
    {
        // if we already have the html key, then do nothing
        if (array_key_exists('html', $this->formMail->message_to_sender)) {
            return;
        }
        // Preflight the message if there's a body key,
        // otherwise return an exception so that the
        // job can be run again.
        if (array_key_exists('body', $this->formMail->message_to_sender)) {
            $message = $this->premailer->html(\View::make('pbc_form_mail_template::layout')->with('data', $this->formMail->message_to_sender)->render());
            $this->formMail->message_to_sender = $message;
            $this->formMail->save();
        } else {
            throw new \Exception('Missing body key in message_to_sender');
        }
    }
}
