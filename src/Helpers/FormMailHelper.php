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

use Pbc\Bandolier\Type\Strings;
use Pbc\Bandolier\Type\Encoded;
use Pbc\Premailer;

/**
 * Class FormMailHelper
 * @package Pbc\FormMail\Helpers
 */
class FormMailHelper
{

    /**
     *
     */
    const RESOURCE_ROOT = 'pbc_form_mail';

    /**
     *
     */
    const RECIPIENT = "recipient";

    /**
     *
     */
    const SENDER = "sender";

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
                self::RECIPIENT => \View::make(self::resourceRoot())
                    ->with('data',
                        array_merge(
                            $data,
                            [
                                'response_type' => self::RECIPIENT,
                                'head' => Encoded::getThingThatIsEncoded($data['head'], self::RECIPIENT)
                            ])
                    )
                    ->render(),
                self::SENDER => \View::make(self::resourceRoot())
                    ->with('data',
                        array_merge(
                            $data,
                            [
                                'response_type' => self::SENDER,
                                'head' => Encoded::getThingThatIsEncoded($data['head'], self::SENDER)
                            ])
                    )
                    ->render(),

            ]
        );
    }

    /**
     * Make Resource string
     * @param $class
     * @param $function
     * @return string
     */
    public function makeResource($class, $function)
    {
        return str_replace('\\', '.', strtolower($class)) . '.' . strtolower($function);
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
        return preg_replace('/[\s+\-]/', '_', $route);
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
            self::SENDER => \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.' . self::SENDER,
                $inject),
            self::RECIPIENT => \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.' . self::RECIPIENT,
                $inject)
        ]);
    }

    /**
     * @param array $data
     * @return array
     */
    private function languageInject(array $data)
    {
        $inject = [
            'form' => Strings::formatForTitle($data['formName']),
            'url' => \Config::get('app.url'),
            'domain' => str_replace_first('www.','', parse_url(\Config::get('app.url'), PHP_URL_HOST)),
            self::RECIPIENT => $data[self::RECIPIENT],
            self::SENDER => $data[self::SENDER],
            "time" => \Carbon\Carbon::now()
        ];
        return $inject;
    }

    /**
     * @return string
     */
    public static function resourceRoot()
    {
        return self::RESOURCE_ROOT . '::body';
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
        if (\Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . self::RECIPIENT) !== self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . self::RECIPIENT
            && \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . self::SENDER) !== self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . self::SENDER
        ) {
            // get data to be injected into the language line.
            $inject = $this->languageInject(array_merge($data, ['formName' => $formName]));
            return json_encode([
                    self::RECIPIENT => \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . self::RECIPIENT,
                        $inject),
                    self::SENDER => \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . self::SENDER,
                        $inject),
                ]
            );
        }

        // default if subject lines were not found
        return Strings::formatForTitle($formName) . ' Form Submission';
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
        try {
            $message = $premailer->html(
                \View::make('pbc_form_mail_template::layout')->with(
                    'data',
                    $data
                )->render()
            );
        } catch (\Exception $ex) {
            $message = [
                'html' => \View::make('pbc_form_mail_template::layout')->with('data', $data)->render(),
                'text' => ''
            ];
        }

        return $message;
    }

    /**
     * @param $form
     * @return $this
     */
    public function sender(&$data, $value = null)
    {
        if (array_key_exists(self::SENDER, $data)) {
            return $this;
        }
        $data[self::SENDER] = $value;
        return $this;
    }

    /**
     * @param $form
     * @return $this
     */
    public function recipient(&$data, $form)
    {
        if (array_key_exists(self::RECIPIENT, $data)) {
            return $this;
        }
        $data[self::RECIPIENT] = $this->makeRecipient($form);
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
