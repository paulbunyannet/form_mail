<?php

namespace Pbc\FormMail\Helpers;

use Pbc\Bandolier\Type\Encoded;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Http\Controllers\FormMailController;
use Pbc\Premailer;
use View;

class MessageHelper
{

    /**
     * Prep message that is sent to recipient for storage
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @return array
     */
    public static function messageToRecipient(FormMail $formMailModel, Premailer $premailer)
    {
        $data = $formMailModel->toArray();
        // Go though each of the keys in the form mail model and
        // check if they are encoded and that there's
        // a key for recipient in it.

        foreach (array_keys($data) as $key) {
            $value = Encoded::getThingThatIsEncoded($data[$key], FormMailController::RECIPIENT);
            if ($value !== $data[$key]) {
                $data[$key] = $value;
            }
        }

        // body of email message
        $data['body'] = View::make(\FormMailHelper::resourceRoot())
            ->with('data', $data)
            ->render();

        if (config('form_mail.queue')) {
            $formMailModel->message_to_recipient = $data;
            $formMailModel->save();
        } else {
            $formMailModel->message_to_recipient = \FormMailHelper::premailer($premailer, $data);
            $formMailModel->save();
        }
    }


    /**
     * Prep confirmation message for storage
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @return array
     */
    public static function messageToSender(FormMail $formMailModel, Premailer $premailer)
    {
        $data = $formMailModel->toArray();
        // Go though each of the keys in the form mail model and
        // check if they are encoded and that there's
        // a key for recipient in it.
        foreach (array_keys($data) as $key) {
            $value = Encoded::getThingThatIsEncoded($data[$key], FormMailController::SENDER);
            if ($value !== $data[$key]) {
                $data[$key] = $value;
            }
        }
        $data['body'] = View::make(\FormMailHelper::resourceRoot())
            ->with('data', $data)
            ->render();

        if (config('form_mail.queue')) {
            $formMailModel->message_to_sender = $data;
            $formMailModel->save();
        } else {
            $formMailModel->message_to_sender = \FormMailHelper::premailer($premailer, $data);
            $formMailModel->save();
        }
    }
}
