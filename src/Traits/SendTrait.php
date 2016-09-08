<?php

namespace Pbc\FormMail\Traits;

use Pbc\FormMail\FormMail;

trait SendTrait {

    /**
     * Send messages out to recipient
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @throws \Exception
     */
    public function send(FormMail $formMailModel)
    {
        // try and email out the message to the recipient.
        // If it fails then return the exception as the
        // response.
        try {
            \Mail::send(
                'pbc_form_mail_template::body',
                ['data' => $formMailModel->message_to_recipient],
                function ($message) use ($formMailModel) {
                    $message->to($formMailModel->recipient)
                        ->subject($formMailModel->subject)
                        ->from(
                            $formMailModel->sender
                        );
                }
            );
            $formMailModel->message_sent_to_recipient = true;
            $formMailModel->save();

            if (\Config::get('form_mail.confirmation')) {
                // try and send out message to sender for conformation.
                // If it fails then return the exception as the
                // response.
                \Mail::send(
                    'pbc_form_mail_template::body',
                    ['data' => $formMailModel->message_to_sender],
                    function ($message) use ($formMailModel) {
                        $message->to($formMailModel->sender)
                            ->subject($formMailModel->subject)
                            ->from($formMailModel->recipient);
                    }
                );
                $formMailModel->confirmation_sent_to_sender = true;
                $formMailModel->save();
            }
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }
    }


}