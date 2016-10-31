<?php

namespace Pbc\FormMail\Helpers;

/**
 * Class Confirmation
 * @package Pbc\FormMail\Helpers
 */
class Confirmation implements HelperContract
{

    /**
     * @param $data
     * @return boolean
     */
    public static function get(array $data = [])
    {
        $key = strtolower(__CLASS__);
        if(array_key_exists($key, $data)) {
            return $data[$key];
        }

        return self::getDefault();
    }

    /**
     * @return mixed
     */
    public static function getDefault()
    {
        return \Config::get('form_mail.confirmation');
    }
}
