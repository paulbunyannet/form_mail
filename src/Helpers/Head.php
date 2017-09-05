<?php

namespace Pbc\FormMail\Helpers;

use Pbc\FormMail\Http\Controllers\FormMailController;

/**
 * Class HeadHelper
 * @package Pbc\FormMail\Helpers
 */
class Head {


    /**
     * @param $data
     * @return string
     */
    public static function makeHead($data)
    {
        $inject = \FormMailHelper::languageInject($data);
        $output = [];
        foreach ([FormMailController::SENDER, FormMailController::RECIPIENT] as $key) {

            $viewPath = FormMailController::RESOURCE_ROOT . '::' . $key . '-head';
            $langPath = \FormMailHelper::resourceRoot() . '.' . \Route::currentRouteName() . '.' . $key;
            $output[$key] = \Lang::get($langPath, $inject);

            if ($output[$key] === $langPath && \View::exists($viewPath)) {
                $output[$key] = \View::make($viewPath)
                    ->with('data', $data + $inject)
                    ->render();
            }
        }
        return json_encode($output);
    }
}
