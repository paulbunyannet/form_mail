<?php

namespace Pbc\FormMail\Traits;

/**
 * Class RulesTrait
 * @package Pbc\FormMail\Traits
 * @codeCoverageIgnore
 */
trait RulesTrait
{

    public function prepRules()
    {
        $this->rules = \FormMailHelper::prepRules($this->rules);
    }

}
