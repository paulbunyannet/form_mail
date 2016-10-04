<?php
/**
 * FormMailHelper
 *
 * Created 5/9/16 11:08 PM
 * Helpers for the form mail controller
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Helpers
 */

namespace Pbc\FormMail\Helpers;

use Pbc\Bandolier\Type\Encoded;
use Pbc\Bandolier\Type\Strings;
use Pbc\FormMail\Decorators\FormMailPremailDecorator;
use Pbc\FormMail\Http\Controllers\FormMailController;
use Pbc\FormMail\Decorators\FormMailDecorator;
use Pbc\FormMail\Generators\FormMailGenerator;
use Pbc\Premailer;

/**
 * Class FormMailHelper
 * @package Pbc\FormMail\Helpers
 */
class FormMailHelper
{



    /**
     * setup resource string
     *
     * @param $class
     * @param $function
     * @return $this
     */
    public function resource(&$data, $class, $function)
    {
        if (array_key_exists('resource', $data)) {
            return $this;
        }
        $data['resource'] = $this->makeResource($class, $function);
        return $this;
    }

    /**
     * add response
     *
     * @param $data
     * @return $this
     */
    public function response(&$data)
    {
        if (array_key_exists('response', $data)) {
            return $this;
        }
        $data['response'] = $this->makeResponse($data);
        return $this;
    }

    /**
     * Make response strings to recipient and sender
     *
     * @param $data
     * @return string
     */
    public function makeResponse($data)
    {
        return json_encode(
            [
                FormMailController::RECIPIENT => \View::make(self::resourceRoot())
                    ->with('data',
                        array_merge(
                            $data,
                            [
                                'response_type' => FormMailController::RECIPIENT,
                                'head' => Encoded::getThingThatIsEncoded($data['head'], FormMailController::RECIPIENT)
                            ])
                    )
                    ->render(),
                FormMailController::SENDER => \View::make(self::resourceRoot())
                    ->with('data',
                        array_merge(
                            $data,
                            [
                                'response_type' => FormMailController::SENDER,
                                'head' => Encoded::getThingThatIsEncoded($data['head'], FormMailController::SENDER)
                            ])
                    )
                    ->render(),
            ]
        );
    }

    /**
     * Make Resource string
     * @param $class
     * @param $method
     * @return string
     */
    public function makeResource($class, $method)
    {
        $generator = new FormMailGenerator(['class' => $class, 'method' => $method]);
        return $generator->resource();
    }

    /**
     * Get branding string
     *
     * @param array $data
     * @return $this
     */
    public function branding(&$data)
    {
        if (array_key_exists('branding', $data)) {
            return $this;
        }

        $data['branding'] = $this->makeBranding($data);
        return $this;
    }

    /**
     * @return array|mixed|null|string
     */
    public function makeBranding($data = [])
    {
        $branding = config('form_mail.branding');

        if ($branding) {
            return $branding;
        } else {
            $formName = array_key_exists('formName', $data) ? $data['formName'] : $this->makeFormName();
            return \Lang::get(
                'pbc_form_mail::body.branding',
                [
                    'form' => Strings::formatForTitle($formName),
                    'domain' => \Config::get('app.url')
                ]
            );
        }

    }

    /**
     * Get form name string
     *
     * @return mixed
     */
    public function makeFormName($route = null)
    {
        if (!$route) {
            $route = \Route::currentRouteName();
        }
        $generator = new FormMailGenerator(['route' => $route]);
        return $generator->formName();
    }

    /**
     * @param $data
     * @return $this
     */
    public function head(&$data)
    {
        if (array_key_exists('head', $data)) {
            return $this;
        }

        $data['head'] = $this->makeHead($data);
        return $this;
    }

    /**
     * @param $data
     * @return string
     */
    public function makeHead($data)
    {
        $inject = $this->languageInject($data);
        return json_encode([
            FormMailController::SENDER => \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.' . FormMailController::SENDER,
                $inject),
            FormMailController::RECIPIENT => \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.' . FormMailController::RECIPIENT,
                $inject)
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    private function languageInject(array $data)
    {
        $decorator = new FormMailDecorator(array_merge($data, [
            'data' => $data,
            'url' => \Config::get('app.url'),
            'recipient' => FormMailController::RECIPIENT,
            'sender' => FormMailController::SENDER,
            'time' => \Carbon\Carbon::now(),
        ]));
        return $decorator->resourceInject();
    }

    /**
     * @return string
     */
    public static function resourceRoot()
    {
        $decorator = new FormMailDecorator(['resource' => FormMailController::RESOURCE_ROOT]);
        return $decorator->resourceRoot();
    }

    /**
     * Get form name string
     *
     * @return $this
     */
    public function formName(&$data)
    {
        if (array_key_exists('formName', $data)) {
            return $this;
        }
        $data['formName'] = $this->makeFormName();
        return $this;
    }

    /**
     * Get request inputs and their matching label input
     *
     * @param Request $request
     * @return $this
     */
    public function fields(&$data, $request)
    {
        if (array_key_exists('fields', $data)) {
            return $this;
        }
        $data['fields'] = $this->makeFields($request);
        return $this;
    }

    /**
     * Make fields array
     *
     * @param $request
     * @return array
     */
    public function makeFields($request)
    {
        $data = [];
        foreach ($request->input('fields') as $field) {
            $label = ($request->input($field . '-label') ? $request->input($field . '-label') : Strings::formatForTitle($field));
            if ($label && $request->input($field)) {
                array_push($data, $this->prepField($label, $request->input($field), $field));
            }
            unset($label);
        }

        return $data;
    }

    /**
     * @param $label
     * @param $value
     * @param null $field
     * @return array
     */
    public function prepField($label, $value, $field = null)
    {
        return ['label' => $label, 'value' => $value, 'field' => $field];
    }

    /**
     * Set Message Subject
     *
     * @param array $data
     * @return $this
     */
    public function subject(&$data)
    {
        if (array_key_exists('subject', $data)) {
            return $this;
        }

        $data['subject'] = $this->makeSubject($data);
        return $this;
    }

    /**
     * Make default subject string
     *
     * @return string
     */
    public function makeSubject(array $data = [])
    {
        // make form name if not already set
        $formName = array_key_exists('formName', $data) ? $data['formName'] : $this->makeFormName();

        // if subject lines for both recipient and sender are set then use them, otherwise resort to defaut
        $output = [];
        foreach([FormMailController::RECIPIENT, FormMailController::SENDER] as $key) {
            // check if language key exists for this route to the recipient/sender key
            if (\Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key) !== self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key) {
                // get data to be injected into the language line.
                $inject = $this->languageInject(array_merge($data, ['formName' => $formName]));
                $output[$key] = \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key, $inject);
            }
        }

        // default if subject lines were not found
        return $output ? json_encode($output) : Strings::formatForTitle($formName) . ' Form Submission';
    }

    /**
     * @param Premailer $premailer
     * @param $data
     * @return array
     */
    public function premailer(Premailer $premailer, $data)
    {
        // send out message to recipient
        // try and send the message with layout through Premailer
        $premailer = new FormMailPremailDecorator($premailer);
        return $premailer->premailer($data);
    }

    /**
     * @param $form
     * @return $this
     */
    public function sender(&$data, $value = null)
    {
        if (array_key_exists(FormMailController::SENDER, $data)) {
            return $this;
        }
        $data[FormMailController::SENDER] = $value;
        return $this;
    }

    /**
     * @param $form
     * @return $this
     */
    public function recipient(&$data, $form)
    {
        if (array_key_exists(FormMailController::RECIPIENT, $data)) {
            return $this;
        }
        $data[FormMailController::RECIPIENT] = $this->makeRecipient($form);
        return $this;
    }

    /**
     * @param $form
     * @return string
     */
    public function makeRecipient($form)
    {
        $formName = $this->makeFormName($form);
        return $formName . '@' . str_replace_first('www.', '', parse_url(\Config::get('app.url'), PHP_URL_HOST));
    }
}
