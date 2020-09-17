<?php

namespace Pbc\FormMail\Traits;

use FormMailHelper;
use Pbc\FormMail\FormMail;
use Pbc\Premailer;

/**
 * Class MessageTrait
 * @package Pbc\FormMail\Traits
 * @codeCoverageIgnore
 */
trait MessageTrait
{

    /**
     * @param \Pbc\FormMail\Facades\FormMailHelper $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public function messageToRecipient(\Pbc\FormMail\Facades\FormMailHelper $formMailModel, Premailer $premailer) : array
    {
        return \Pbc\FormMail\Facades\FormMailHelper::messageToRecipient($formMailModel, $premailer);
    }

    /**
     * @param \Pbc\FormMail\Facades\FormMailHelper $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public function messageToSender(\Pbc\FormMail\Facades\FormMailHelper $formMailModel, Premailer $premailer) : array
    {
        return \Pbc\FormMail\Facades\FormMailHelper::messageToSender($formMailModel, $premailer);
    }
}
