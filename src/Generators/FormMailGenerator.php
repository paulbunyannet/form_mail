<?php
namespace Pbc\FormMail\Generators;

/**
 * FormMailGenerator
 *
 * Created 10/4/16 9:29 AM
 * Form Mail generator
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Decorators
 * @subpackage Subpackage
 */
class FormMailGenerator
{

    public function __construct(array $data = [])
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    public function resource()
    {
        return str_replace('\\', '.', strtolower($this->class)) . '.' . strtolower($this->method);
    }

    public function formName()
    {
        return preg_replace('/[\s+\-]/', '_', $this->route);
    }


}