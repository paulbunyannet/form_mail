<?php

namespace Pbc\FormMail\Helpers;

use Pbc\FormMail\Http\Controllers\FormMailController;

/**
 * Class HeadHelper
 * @package Pbc\FormMail\Helpers
 */
class Subject {


    /**
     * @param $data
     * @return string
     */
    public static function makeSubject($data)
    {
        $inject = \FormMailHelper::languageInject($data);
        $output = [];
        foreach ([FormMailController::SENDER, FormMailController::RECIPIENT] as $key) {

            $viewPath = FormMailController::RESOURCE_ROOT . '::' . $key . '-subject';
            $langPath = \FormMailHelper::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key;
            $output[$key] = \Lang::get($langPath, $inject);

            if ($output[$key] === $langPath && \View::exists($viewPath)) {
                $output[$key] = \View::make($viewPath)
                    ->with('data', $data)
                    ->render();
            }
        }
        return json_encode($output);
    }
}
