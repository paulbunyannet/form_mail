<?php

namespace Pbc\FormMail\Traits;

use Pbc\FormMail\FormMail;

/**
 * Class SendTrait
 * @package Pbc\FormMail\Traits
 * @codeCoverageIgnore
 */
trait SendTrait
{

    /**
     * Send messages out to recipient
     *
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @throws \Exception
     */
    public function send(FormMail $formMailModel)
    {
        return\FormMailHelper::send($formMailModel);
    }


}