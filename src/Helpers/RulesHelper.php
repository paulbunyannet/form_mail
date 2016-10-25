<?php

namespace Pbc\FormMail\Helpers;

class RulesHelper {

    public static function prepRules(array $rules = [])
    {
        return array_merge(
            $rules,
            \Config::get('form_mail.rules') ?  \Config::get('form_mail.rules') : [],
            \Config::get('route_rules.' . \Route::currentRouteName()) ? \Config::get('route_rules.' . \Route::currentRouteName()) : []
        );
    }

}