<?php

namespace Pbc\FormMail\Helpers;

use Pbc\FormMail\FormMail;

/**
 * Class Greeting
 * @package Pbc\FormMail\Helpers
 */
class Greeting {

    /**
     * Make a greeting string
     *
     * @param array $data
     * @return string
     */
    public static function makeGreeting(array $data) : string
    {
        if (!array_key_exists('greeting', $data)) {
            $data['greeting'] = self::defaultGreeting();
        }
        $m = new \Mustache_Engine();
        return $m->render($data['greeting'], $data);
    }

    /**
     * @return array|null|string
     */
    public static function defaultGreeting()
    {
        return \Lang::get(\FormMailHelper::resourceRoot().'.greeting');
    }
}
