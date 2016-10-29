<?php

namespace Pbc\FormMail\Traits;

use FormMailHelper;
use Pbc\FormMail\FormMail;
use Pbc\Premailer;

trait MessageTrait
{

    /**
     * Prep message that is sent to recipient for storage
     *
     * @param FormMailHelper|FormMail $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public function messageToRecipient(FormMailHelper $formMailModel, Premailer $premailer)
    {
        return \FormMailHelper::messageToRecipient($formMailModel, $premailer);
    }

    /**
     * Prep confirmation message for storage
     *
     * @param FormMailHelper|FormMail $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public function messageToSender(FormMailHelper $formMailModel, Premailer $premailer)
    {
        return \FormMailHelper::messageToSender($formMailModel, $premailer);
    }
}
