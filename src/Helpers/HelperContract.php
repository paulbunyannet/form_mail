<?php
/**
 * HelperContract
 *
 * Created 10/31/16 4:33 PM
 * Contract for helper classes
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Helpers
 */
namespace Pbc\FormMail\Helpers;


/**
 * Class Confirmation
 * @package Pbc\FormMail\Helpers
 */
interface HelperContract
{
    /**
     * @return mixed
     */
    public static function getDefault();

    /**
     * @param array $data
     * @return mixed
     */
    public static function get(array $data = []);
}