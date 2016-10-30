<?php

namespace Pbc\FormMail\Helpers;

use Pbc\FormMail\Http\Controllers\FormMailController;

/**
 * Class SenderHelper
 * @package Pbc\FormMail\Helpers
 */
class Sender {

    /**
     * @param $data
     * @param null $sender email to use as sender, either a valid email address or the username for username@example.com
     * @return array|null|string
     */
    public static function makeSender($data = [], $sender = null, $url = null)
    {
        if(array_key_exists(FormMailController::SENDER, $data) && filter_var($data[FormMailController::SENDER], FILTER_VALIDATE_EMAIL)) {
            return $data[FormMailController::SENDER];
        }

        if (!$sender) {
            $sender = \Request::input('email');
        }

        if (filter_var($sender, FILTER_VALIDATE_EMAIL)) {
            return $sender;
        }

        if(!$url) {
            $url =\Config::get('app.url');
        }

        return $sender . '@' . str_replace_first('www.', '', parse_url($url, PHP_URL_HOST));
    }
}
