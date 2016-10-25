<?php

namespace Pbc\FormMail\Facades;

use Illuminate\Support\Facades\Facade;

class FormMailHelper extends Facade {
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'formMailHelper';
    }
}