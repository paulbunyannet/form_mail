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
     * @return mixed
     */
    public function formName()
    {
        return preg_replace('/[\s+\-]/', '_', \Route::currentRouteName());
    }

    /**
     * Get request inputs and their matching label input
     *
     * @param Request $request
     * @return mixed
     */
    public function requestFields($request, &$data)
    {
        foreach ($request->input('fields') as $field) {
            $label = ($request->input($field . '-label') ? $request->input($field . '-label') : Strings::formatForTitle($field));
            if ($label && $request->input($field)) {
                $data['fields'][] = $this->prepField($label, $request->input($field), $field);
            }
            unset($label);
        }
    }

    /**
     * Set Message Subject
     *
     * @param array $data
     */
    public function subject(&$data = [])
    {
        $data['subject'] = Strings::formatForTitle($this->formName()) . ' Form Submission';
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
     * @return string
     */
    public function recipient($form)
    {
        $formName = preg_replace('/[\s+\-]/', '_', $form);
        return $formName . '@' . str_replace_first('www.', '', parse_url(\Config::get('app.url'), PHP_URL_HOST));
    }
    
}
