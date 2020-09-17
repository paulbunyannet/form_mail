<?php
namespace Pbc\FormMail\Decorators;

/**
 * FormMailDecorator
 *
 * Created 10/4/16 9:29 AM
 * Form Mail decorator
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Decorators
 * @subpackage Subpackage
 */

use Pbc\Bandolier\Type\Strings;

/**
 * @property string $url app url
 * @property string $recipient recipient array key
 * @property string $sender sender array key
 * @property string $time timestamp
 * @property string $resource resource root for finding resources
 * @property array $data array of data for injection into views/language
 *
 */
class FormMailDecorator
{
    /**
     * FormMailDecorator constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Build resource injection fields
     *
     * @return array
     */
    public function resourceInject() : array
    {
        return [
            'form' => Strings::formatForTitle($this->formName),
            'url' => $this->url,
            'domain' => str_replace_first('www.', '', parse_url($this->url, PHP_URL_HOST)),
            $this->recipient => $this->recipient,
            $this->sender => $this->sender,
            "time" => $this->time
        ];
    }

    /**
     * Build resource root string
     *
     * @return string
     */
    public function resourceRoot() : string
    {
        return $this->resource . '::body';
    }
}
