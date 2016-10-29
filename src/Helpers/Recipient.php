<?php

namespace Pbc\FormMail\Helpers;

/**
 * Class RecipientHelper
 * @package Pbc\FormMail\Helpers
 */
class Recipient {


    /**
     * Make recipient string
     *
     * @param $form
     * @param null $url
     * @return string
     */
    public static function makeRecipient($form, $url=null)
    {
        if (!$url) {
            $url = \Config::get('app.url');
        }

        $formName = \FormMailHelper::makeFormName($form);
        return $formName . '@' . str_replace_first('www.', '', parse_url($url, PHP_URL_HOST));
    }
}
