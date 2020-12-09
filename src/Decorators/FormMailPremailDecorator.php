<?php
namespace Pbc\FormMail\Decorators;

/**
 * FormMailPremailDecorator
 *
 * Created 10/4/16 10:47 AM
 * Decorator for inlining css in messages
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Decorators
 */

use Pbc\Premailer;

class FormMailPremailDecorator
{

    protected Premailer $premailer;

    public function __construct(Premailer $premailer, array $data = [])
    {
        $this->premailer = $premailer;

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * Send message through premailer
     *
     * @param array $data
     * @return array
     */
    public function premailer(array $data = []) : array
    {
        try {
            return $this->premailer->html(
                \View::make('pbc_form_mail_template::layout')->with(
                    'data',
                    $data
                )->render()
            );
        } catch (\Exception $ex) {
            return [
                'html' => \View::make('pbc_form_mail_template::layout')->with('data', $data)->render(),
                'text' => ''
            ];
        }
    }
}
