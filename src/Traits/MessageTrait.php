<?php

namespace Pbc\FormMail\Traits;

use Pbc\Bandolier\Type\Strings;
use Pbc\FormMail\FormMail;
use Carbon\Carbon;

trait MessageTrait {

    /**
     * Prep message that is sent to recipient for storage
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @return array
     */
    public function messageToRecipient(FormMail $formMailModel)
    {

        $data = $formMailModel->toArray();
        // headline for email message
        $data['head'] = \Lang::get(
            'pbc_form_mail::body.' . \Route::currentRouteName() . '.recipient',
            [
                'form' => Strings::formatForTitle($formMailModel->form),
                'domain' => parse_url(
                    \App::make('url')->to('/'),
                    PHP_URL_HOST
                ),
                'time' => Carbon::now()
            ]
        );
        // body of email message
        $data['body'] = \View::make('pbc_form_mail::body')
            ->with('data', $data)
            ->render();

        // subject of message
        $data['subject'] = $this->helper->getThingThatIsEncoded($data['subject'], 'recipient');

        if (config('form_mail.queue')) {
            $formMailModel->message_to_recipient = $data;
            $formMailModel->save();
        } else {
            $formMailModel->message_to_recipient = $this->helper->premailer($this->premailer, $data);
            $formMailModel->save();
        }
    }


    /**
     * Prep confirmation message for storage
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @return array
     */
    public function messageToSender(FormMail $formMailModel)
    {
        $data = $formMailModel->toArray();
        $data['head'] = \Lang::get(
            'pbc_form_mail::body.' . \Route::currentRouteName() . '.confirmation',
            [
                'form' => Strings::formatForTitle($formMailModel->form),
                'recipient' => $formMailModel->recipient,
            ]
        );
        $data['body'] = \View::make('pbc_form_mail::body')
            ->with('data', $data)
            ->render();

        // subject of message
        $data['subject'] = $this->helper->getThingThatIsEncoded($data['subject'], 'sender');

        if (config('form_mail.queue')) {
            $formMailModel->message_to_sender = $data;
            $formMailModel->save();
        } else {
            $formMailModel->message_to_sender = $this->helper->premailer($this->premailer, $data);
            $formMailModel->save();
        }

    }
}
