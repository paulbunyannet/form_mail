<?php

namespace Pbc\FormMail\Helpers;

use Pbc\Bandolier\Type\Encoded;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\FormMail\Http\Controllers\FormMailController;

class SendHelper
{

    /**
     * Send messages out to recipient
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @throws \Exception
     */
    public static function send(FormMail $formMailModel)
    {
        // try and email out the message to the recipient.
        // If it fails then return the exception as the
        // response.
        try {
            $recipientSubject = Encoded::getThingThatIsEncoded($formMailModel->subject, FormMailController::RECIPIENT);
            $recipientTo = $formMailModel->{FormMailController::RECIPIENT};
            $recipientFrom = $formMailModel->{FormMailController::SENDER};
            \Mail::send(
                'pbc_form_mail_template::body',
                ['data' => $formMailModel->message_to_recipient],
                function ($message) use ($recipientSubject, $recipientTo, $recipientFrom) {
                    $message->to($recipientTo)
                        ->subject($recipientSubject)
                        ->from($recipientFrom);
                }
            );
            $formMailModel->message_sent_to_recipient = true;
            $formMailModel->save();

            if (\Config::get('form_mail.confirmation')) {
                // try and send out message to sender for conformation.
                // If it fails then return the exception as the
                // response.
                $senderSubject = Encoded::getThingThatIsEncoded($formMailModel->subject, FormMailController::SENDER);
                $senderTo = $formMailModel->{FormMailController::SENDER};
                $senderFrom = $formMailModel->{FormMailController::RECIPIENT};
                \Mail::send(
                    'pbc_form_mail_template::body',
                    ['data' => $formMailModel->message_to_sender],
                    function ($message) use ($senderSubject, $senderTo, $senderFrom) {
                        $message->to($senderTo)
                            ->subject($senderSubject)
                            ->from($senderFrom);
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