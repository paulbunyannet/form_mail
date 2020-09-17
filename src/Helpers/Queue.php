<?php

namespace Pbc\FormMail\Helpers;

use Illuminate\Contracts\Bus\Dispatcher;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Jobs\FormMailSendMessage;
use Pbc\FormMail\Jobs\FormMailSendConfirmationMessage;

/**
 * Class Queue
 * @package Pbc\FormMail\Helpers
 */
class Queue implements HelperContract
{
    /**
     * @param FormMail $formMailModel
     * @param \Pbc\Premailer $premailer
     * @param int $defaultDelay
     */
    public static function queue(FormMail $formMailModel, \Pbc\Premailer $premailer, int $defaultDelay=10)
    {
        $formMailSendMessage =  (new FormMailSendMessage($formMailModel, $premailer))->delay(config('form_mail.delay.send_message', $defaultDelay));
        app(Dispatcher::class)->dispatch($formMailSendMessage);
        if ($formMailModel->confirmation) {
            $formMailSendConfirmationMessage = (new FormMailSendConfirmationMessage($formMailModel, $premailer))->delay(config('form_mail.delay.send_confirmation', $defaultDelay));
            app(Dispatcher::class)->dispatch($formMailSendConfirmationMessage);

        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public static function get(array $data = []) : bool
    {
        $classKey = strtolower(__CLASS__);
        if(array_key_exists($classKey, $data)) {
            return $data[$classKey];
        }

        return self::getDefault();
    }

    /**
     * @return bool
     */
    public static function getDefault() : bool
    {
        return \Config::get('form_mail.queue');
    }
}
