<?php

namespace Pbc\FormMail\Helpers;

use Illuminate\Contracts\Bus\Dispatcher;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Jobs\FormMailSendMessage;
use Pbc\FormMail\Jobs\FormMailSendConfirmationMessage;

class Queue {


    /**
     * Queue the messages for sending on next queue process
     *
     * @param FormMail $formMailModel
     */
    public static function queue(FormMail $formMailModel, \Pbc\Premailer $premailer, $defaultDelay=10)
    {
        $formMailSendMessage =  (new FormMailSendMessage($formMailModel, $premailer))->delay(config('form_mail.delay.send_message', $defaultDelay));
        app(Dispatcher::class)->dispatch($formMailSendMessage);
        if (config('form_mail.confirmation')) {
            $formMailSendConfirmationMessage = (new FormMailSendConfirmationMessage($formMailModel, $premailer))->delay(config('form_mail.delay.send_confirmation', $defaultDelay));
            app(Dispatcher::class)->dispatch($formMailSendConfirmationMessage);

        }
    }
}
