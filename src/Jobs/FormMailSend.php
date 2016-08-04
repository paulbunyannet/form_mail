<?php
/**
 * FormMailSend
 *
 * Created 6/23/16 10:01 AM
 * vase class for FormMailSend* jobs
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Jobs
 */

namespace Pbc\FormMail\Jobs;


use App\Jobs\Job;
use Pbc\FormMail\FormMail;
use Pbc\Premailer;

class FormMailSend extends Job
{

    public $formMail;
    /**
     * @var Premailer Premailer
     */
    public $preflight;

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
        if (!array_key_exists('html', $this->formMail->message_to_recipient) && !array_key_exists('text', $this->formMail->message_to_recipient)) {
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
        if (!array_key_exists('html', $this->formMail->message_to_sender) && !array_key_exists('text', $this->formMail->message_to_sender)) {
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

    protected function preflight($string)
    {
        $message = $this->preflight->html(\View::make('pbc_form_mail_template::layout')->with('data', $this->formMail->{$string})->render());
        $this->formMail->{$string} = $message;
        $this->formMail->save();

        return $this;
    }

}