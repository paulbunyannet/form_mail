<?php

namespace Pbc\FormMail\Traits;

trait RulesTrait {

    public function prepRules()
    {
        $this->rules = \FormMailHelper::prepRules($this->rules);
    }

}