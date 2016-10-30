<?php

namespace Pbc\FormMail\Traits;

use Pbc\FormMail\FormMail;

/**
 * Class QueueTrait
 * @package Pbc\FormMail\Traits
 * @codeCoverageIgnore
 */
trait QueueTrait {


    /**
     * Queue the messages for sending on next queue process
     *
     * @param FormMail $formMailModel
     */
    public function queue(FormMail $formMailModel, \Pbc\Premailer $premailer, $defaultDelay=10)
    {
        return \FormMailHelper::queue($formMailModel, $premailer, $defaultDelay);
    }
}
