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
use Pbc\Premailer;

/**
 * Class FormMailHelper
 * @package Pbc\FormMail\Helpers
 */
class FormMailHelper
{
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
     * setup resource string
     *
     * @param $class
     * @param $function
     * @return string
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
     * Make Resource string
     * @param $class
     * @param $function
     * @return string
     */
    public function makeResource($class, $function) {
        return str_replace('\\', '.', strtolower($class)) . '.' . strtolower($function);
    }

    /**
     * Get branding string
     *
     * @param array $data
     */
    public function branding(&$data = [])
    {
        $branding = config('form_mail.branding');
        if ($branding) {
            $data['branding'] = $branding;
        } else {
            $data['branding'] = \Lang::get(
                'pbc_form_mail::body.branding',
                [
                    'form' => Strings::formatForTitle($data['formName']),
                    'domain' => \Config::get('app.url')
                ]
            );
        }
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
     * Get request inputs and their matching label input
     *
     * @param Request $request
     * @return mixed
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
     * Set Message Subject
     *
     * @param array $data
     */
    public function subject(&$data = [])
    {
        $data['subject'] = Strings::formatForTitle($this->makeFormName()) . ' Form Submission';
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
    public function recipient(&$data, $form)
    {
        if (array_key_exists('recipient', $data)) {
            return $this;
        }
        $data['recipient'] = $this->makeRecipient($form);
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
