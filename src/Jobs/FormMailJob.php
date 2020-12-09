<?php
/**
 * FormMailSend
 *
 * Created 6/23/16 10:01 AM
 * Base class for FormMailSend* jobs
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Jobs
 */

namespace Pbc\FormMail\Jobs;


use Illuminate\Bus\Queueable;
use Pbc\FormMail\FormMail;
use Pbc\Premailer;

class FormMailJob
{
    use Queueable;

    public FormMail $formMail;

    public Premailer $preflight;

    public array $preflighted = [];

    /**
     * FormMailSend constructor.
     * @param FormMail $formMail
     * @param Premailer $premailer
     */
    public function __construct(FormMail $formMail, Premailer $premailer)
    {
        $this->formMail = $formMail;
        $this->preflight = $premailer;
    }

    /**
     * Validate message to recipient. It needs to
     * have a key "html" or "text".
     *
     * @throws \Exception
     */
    public function validateMessageToRecipient()
    {
        if (!array_key_exists('html', $this->formMail->message_to_recipient) && !array_key_exists('text',
                $this->formMail->message_to_recipient)
        ) {
            throw new \Exception('Missing html and/or text keys in message_to_recipient');
        }

        return $this;
    }

    /**
     * Validate message to sender. It needs to
     * have a key "html" or "text".
     *
     * @throws \Exception
     */
    public function validateMessageToSender()
    {
        if (!array_key_exists('html', $this->formMail->message_to_sender) && !array_key_exists('text',
                $this->formMail->message_to_sender)
        ) {
            throw new \Exception('Missing html and/or text keys in message_to_sender');
        }

        return $this;
    }

    /**
     * Validate the recipient address
     *
     * @throws \Exception
     */
    public function validateRecipient()
    {

        if (!filter_var($this->formMail->recipient, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid recipient address');
        }

        return $this;

    }

    /**
     * Validate the sender address
     *
     * @throws \Exception
     */
    public function validateSender()
    {

        if (!filter_var($this->formMail->sender, FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Invalid sender address');
        }

        return $this;
    }

    /**
     * Preflight html email message
     *
     * @param $key
     * @return $this
     */
    public function preflight($key)
    {
        $body = \View::make('pbc_form_mail_template::layout')
            ->with('data', $this->formMail->{$key})
            ->render();
        try {
            $message = $this->preflight->html($body);
        } catch (\Exception $ex) {
            $message = ['html' => $body, 'text' => $body];
        }
        $message = array_merge($this->formMail->{$key}, $message);
        $this->preflighted[$key] = $message;
        $this->formMail->{$key} = $message;
        $this->formMail->save();

        return $this;
    }

    /**
     * Do check if there was a failure when trying to send mail
     *
     * @return $this
     * @throws \Exception
     */
    protected function validateMailSent()
    {
        if (count(\Mail::failures()) > 0) {
            // @codeCoverageIgnoreStart
            $exceptionMessage = "There was one or more mail failures. They were: \n\r";
            foreach (\Mail::failures() as $email_address) {
                $exceptionMessage .= " - $email_address \n\r";
            }
            throw new \Exception($exceptionMessage);
            // @codeCoverageIgnoreEnd
        }

        return $this;

    }

}
