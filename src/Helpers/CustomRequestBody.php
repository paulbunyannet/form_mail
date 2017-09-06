<?php
/**
 * CustomRequestBody
 *
 * Created 9/6/17 2:10 PM
 * For getting the custom_request_body key from request
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Helpers
 */

namespace Pbc\FormMail\Helpers;


/**
 * Class CustomRequestBody
 * @package Pbc\FormMail\Helpers
 */
class CustomRequestBody implements HelperContract
{
    /**
     *
     */
    public static function getDefault()
    {
        return \Request::instance()->query('custom_request_body');
    }

    /**
     * @param array $data
     * @return array|mixed|string
     */
    public static function get(array $data = [])
    {
        $classKey = 'custom_request_body';
        if (array_key_exists('custom_request_body', $data)) {
            return $data[$classKey];
        }

        return self::getDefault();
    }
}