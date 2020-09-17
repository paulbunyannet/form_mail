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
        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function resource() : string
    {
        return str_replace('\\', '.', strtolower($this->class)) . '.' . strtolower($this->method);
    }

    public function formName() : string
    {
        return preg_replace('/[\s+\-]/', '_', $this->route);
    }
}
