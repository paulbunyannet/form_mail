<?php

namespace Pbc\FormMail\Traits;

use Pbc\FormMail\FormMail;
use Pbc\FormMail\Jobs\FormMailSendMessage;
use Pbc\FormMail\Jobs\FormMailSendConfirmationMessage;

trait QueueTrait {


    /**
     * Queue the messages for sending on next queue process
     *
     * @param FormMail $formMailModel
     */
    public function queue(FormMail $formMailModel)
    {

        $formMailSendMessage =  (new FormMailSendMessage($formMailModel, $this->premailer))->delay(config('form_mail.delay.send_message', 10));
        $this->dispatch($formMailSendMessage);
        if (config('form_mail.confirmation')) {
            $formMailSendConfirmationMessage = (new FormMailSendConfirmationMessage($formMailModel, $this->premailer))->delay(config('form_mail.delay.send_confirmation', 10));
            $this->dispatch($formMailSendConfirmationMessage);
        }
    }
}
