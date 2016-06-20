<?php

namespace Pbc\FormMail\Jobs;

use App\Jobs\Job;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Pbc\FormMail\FormMail;

class FormMailSendMessage extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    public $formMail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(FormMail $formMail)
    {
        $this->formMail = $formMail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->formMail->message_sent_to_recipient) {
            \Mail::send('pbc_form_mail_template::body', ['data' => $this->formMail->message_to_recipient], function ($message) {
                $message->to($this->formMail->recipient)
                    ->from($this->formMail->sender)
                    ->subject($this->formMail->subject);
            });

            $this->formMail->message_sent_to_recipient = 1;
            $this->formMail->save();
        }
    }
}
