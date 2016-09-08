<?php

namespace Pbc\FormMail\Traits;

trait RulesTrait {

    public function prepRules()
    {
        $this->rules = array_merge(
            $this->rules,
            \Config::get('form_mail.rules') ?  \Config::get('form_mail.rules') : [],
            \Config::get('route_rules.' . \Route::currentRouteName()) ? \Config::get('route_rules.' . \Route::currentRouteName()) : []
        );
    }

}