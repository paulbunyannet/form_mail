<?php

namespace Pbc\FormMail\Helpers;

/**
 * Class Confirmation
 * @package Pbc\FormMail\Helpers
 */
class Confirmation implements HelperContract
{
    /**
     * @param array $data
     * @return bool
     */
    public static function get(array $data = []) : bool
    {
        $key = strtolower(__CLASS__);
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return self::getDefault();
    }

    /**
     * @return bool
     */
    public static function getDefault() : bool
    {
        return \Config::get('form_mail.confirmation');
    }
}
