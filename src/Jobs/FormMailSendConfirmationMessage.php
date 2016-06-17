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
class FormMailSendConfirmationMessage extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var FormMail
     */
    public $formMail;
    /**
     * @var FormMailHelper
     */
    public $helper;

    /**
     * @var Premailer
     */
    public $premailer;
    public $doConfirmation;

    /**
     * Create a new job instance.
     *
     * @param FormMail $formMail
     * @param Premailer $premailer
     * @param FormMailHelper $helper
     */
    public function __construct(FormMail $formMail, Premailer $premailer, FormMailHelper $helper)
    {
        $this->formMail = $formMail;
        $this->premailer = $premailer;
        $this->helper = $helper;
        $this->doConfirmation = \Config::get('form_mail.confirmation');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        if (!$this->formMail->confirmation_sent_to_sender && $this->doConfirmation) {
            $data = [];
            $data['subject'] = $this->formMail->subject . ' confirmation';
            $data['form'] = $this->formMail->form;
            $data['head'] = \Lang::get('pbc_form_mail::body.success', [
                'form' => Strings::formatForTitle($data['form']),
                'recipient' => $this->formMail->recipient,
            ]);
            $data['fields'] = $this->formMail->fields;
            $data['body'] = \View::make('pbc_form_mail::body')->with('data', $data)->render();
            $message = $this->helper->premailer($this->premailer, $data);

            $mailer->send('pbc_form_mail_template::body', ['data' => $message], function ($message) use ($data) {
                $message->to($this->formMail->sender)
                    ->from($this->formMail->recipient)
                    ->subject($data['subject']);
            });

            $this->formMail->confirmation_sent_to_sender = true;
            $this->formMail->save();
        }
    }
}
