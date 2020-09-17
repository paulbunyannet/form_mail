<?php

namespace Pbc\FormMail\Traits;

use Pbc\FormMail\Facades\FormMailHelper;
use Pbc\FormMail\FormMail;

/**
 * Class QueueTrait
 * @package Pbc\FormMail\Traits
 * @codeCoverageIgnore
 */
trait QueueTrait {


    /**
     * @param FormMail $formMailModel
     * @param \Pbc\Premailer $premailer
     * @param int $defaultDelay
     * @return mixed
     */
    public function queue(FormMail $formMailModel, \Pbc\Premailer $premailer, int $defaultDelay = 10) : FormMailHelper
    {
        return \FormMailHelper::queue($formMailModel, $premailer, $defaultDelay);
    }
}
