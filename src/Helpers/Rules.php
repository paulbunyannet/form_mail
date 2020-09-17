<?php

namespace Pbc\FormMail\Helpers;

class Rules
{
    /**
     * @param array $rules
     * @return array
     */
    public static function prepRules(array $rules = []) : array
    {
        return array_merge(
            $rules,
            \Config::get('form_mail.rules') ?  \Config::get('form_mail.rules') : [],
            \Config::get('route_rules.' . \Route::currentRouteName()) ? \Config::get('route_rules.' . \Route::currentRouteName()) : []
        );
    }
}
