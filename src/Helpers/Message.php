<?php

namespace Pbc\FormMail\Helpers;

use Carbon\Carbon;
use Pbc\Bandolier\Type\Arrays;
use Pbc\Bandolier\Type\Encoded;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Http\Controllers\FormMailController;
use Pbc\Premailer;
use View;

/**
 * Class MessageHelper
 * @package Pbc\FormMail\Helpers
 */
class Message
{

    /**
     * @param FormMail $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public static function messageToRecipient(FormMail $formMailModel, Premailer $premailer) : array
    {
        $data = $formMailModel->toArray();
        // Go though each of the keys in the form mail model and
        // check if they are encoded and that there's
        // a key for recipient in it.

        foreach (array_keys($data) as $key) {
            $value = is_string($data[$key]) ? Encoded::getThingThatIsEncoded($data[$key], FormMailController::RECIPIENT) : $data[$key];
            // @codeCoverageIgnoreStart
            if ($value !== $data[$key]) {
                $data[$key] = $value;
            }
            // @codeCoverageIgnoreEnd
        }
        // body of email message
        $data['head'] = $data['head'][FormMailController::RECIPIENT];
        $data['subject'] = $data['subject'][FormMailController::RECIPIENT];
        $data['body'] = View::make(\FormMailHelper::resourceRoot())
            ->with('data', $data)
            ->render();

        if ($formMailModel->queue) {
            $formMailModel->message_to_recipient = $data;
            $formMailModel->save();
        } else {
            $formMailModel->message_to_recipient = array_merge(
                $data,
                \FormMailHelper::premailer($premailer, $data)
            );
            $formMailModel->save();
        }

        return $data;
    }


    /**
     * @param FormMail $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public static function messageToSender(FormMail $formMailModel, Premailer $premailer) : array
    {
        $data = $formMailModel->toArray();
        // Go though each of the keys in the form mail model and
        // check if they are encoded and that there's
        // a key for recipient in it.
        foreach (array_keys($data) as $key) {
            $value = is_string($data[$key]) ? Encoded::getThingThatIsEncoded($data[$key], FormMailController::SENDER) : $data[$key];
            // @codeCoverageIgnoreStart
            if ($value !== $data[$key]) {
                $data[$key] = $value;
            }
            // @codeCoverageIgnoreEnd
        }
        $data['head'] = $data['head'][FormMailController::SENDER];
        $data['subject'] = $data['subject'][FormMailController::SENDER];
        $data['body'] = View::make(\FormMailHelper::resourceRoot())
            ->with('data', $data)
            ->render();
        if ($formMailModel->queue) {
            $formMailModel->message_to_sender = $data;
            $formMailModel->save();
        } else {
            $formMailModel->message_to_sender = array_merge(
                $data,
                \FormMailHelper::premailer($premailer, $data)
            );
            $formMailModel->save();
        }

        return $data;
    }

    /**
     * @param array $data
     * @return FormMail
     * @throws \Exception
     */
    public static function makeMessage(array $data = []) : FormMail
    {
        // make record in formMail model
        \DB::beginTransaction();
        try {
            $defaults = [
                'form' => __FUNCTION__,
                'formName' => \FormMailHelper::makeFormName(),
                'resource' => \FormMailHelper::makeResource(__CLASS__, __FUNCTION__),
                'sender' => '',
                'recipient' => '',
                'name' => '',
                'fields' => [],
                'subject' => '',
                'branding' => '',
                'head' => '',
                'body' => '',
                'message_sent_to_recipient' => false,
                'confirmation_sent_to_sender' => false,
                'premailer' => new Premailer(),
                'time' => Carbon::now(),
                'greeting' => Greeting::defaultGreeting(),
                'confirmation' => Confirmation::getDefault(),
                'queue' => Queue::getDefault(),
                'custom_request_body' => CustomRequestBody::getDefault(),
            ];
            $data = Arrays::defaultAttributes($defaults, $data);

            $data['greeting'] = Greeting::makeGreeting($data);
            $data['head'] = Head::makeHead($data);
            $data['subject'] = Subject::makeSubject($data);

            $formMailModelClass = \App::make(FormMail::class);
            $formMailModel = $formMailModelClass::create(
                Arrays::defaultAttributes(array_flip($formMailModelClass->columns()), $data)
            );
            self::messageToRecipient($formMailModel, $data['premailer']);
            self::messageToSender($formMailModel, $data['premailer']);
        } catch (\Exception $ex) {
            // @codeCoverageIgnoreStart
            \DB::rollBack();
            throw $ex;
            // @codeCoverageIgnoreEnd
        }
        // if we should be queueing this message and confirmation,
        // then do that here, otherwise email out the messages
        // below.
        try {
            if ($formMailModel->queue) {
                Queue::queue($formMailModel, $data['premailer']);
            } else {
                Send::send($formMailModel);
            }
        } catch (\Exception $ex) {
            throw $ex;
        }
        \DB::commit();
        return $formMailModel;
    }
}
