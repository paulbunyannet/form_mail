<?php
/**
 * FormMailHelper
 *
 * Created 5/9/16 11:08 PM
 * Helpers for the form mail
 *
 * @author Nate Nolting <naten@paulbunyan.net>
 * @package Pbc\FormMail\Helpers
 */

namespace Pbc\FormMail\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Pbc\Bandolier\Type\Encoded;
use Pbc\Bandolier\Type\Strings;
use Pbc\FormMail\Decorators\FormMailDecorator;
use Pbc\FormMail\Decorators\FormMailPremailDecorator;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Generators\FormMailGenerator;
use Pbc\FormMail\Http\Controllers\FormMailController;
use Pbc\Premailer;

/**
 * Class FormMailHelper
 * @package Pbc\FormMail\Helpers
 */
class FormMailHelper
{
    /**
     * @param FormMail $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public function messageToRecipient(FormMail $formMailModel, Premailer $premailer) : array
    {
        return Message::messageToRecipient($formMailModel, $premailer);
    }

    /**
     * @param FormMail $formMailModel
     * @param Premailer $premailer
     * @return array
     */
    public function messageToSender(FormMail $formMailModel, Premailer $premailer) : array
    {
        return Message::messageToSender($formMailModel, $premailer);

    }

    /**
     * @param array $rules
     * @return array
     */
    public function prepRules(array $rules = []) : array
    {
        return Rules::prepRules($rules);
    }

    /**
     * @param array $data
     * @param $class
     * @param $function
     * @return $this
     */
    public function resource(array &$data, $class, $function) : FormMailHelper
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
     * @param $method
     * @return string
     */
    public function makeResource($class, $method) : string
    {
        $generator = new FormMailGenerator(['class' => $class, 'method' => $method]);
        return $generator->resource();
    }

    /**
     * add response
     *
     * @param $data
     * @return $this
     */
    public function response(array &$data) : FormMailHelper
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
    public function makeResponse(array $data) : string
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
     * @return string
     */
    public static function resourceRoot() : string
    {
        $decorator = new FormMailDecorator(['resource' => FormMailController::RESOURCE_ROOT]);
        return $decorator->resourceRoot();
    }

    /**
     * Get branding string
     *
     * @param array $data
     * @return $this
     */
    public function branding(array &$data) : FormMailHelper
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
    public function makeBranding(array $data = [])
    {
        $branding = config('form_mail.branding');

        if ($branding) {
            return $branding;
        }
        $formName = array_key_exists('formName', $data) ? $data['formName'] : $this->makeFormName();
        return \Lang::get(
            'pbc_form_mail::body.branding',
            [
                'form' => Strings::formatForTitle($formName),
                'domain' => \Config::get('app.url')
            ]
        );

    }

    /**
     * @param null $route
     * @return string
     */
    public function makeFormName($route = null) : string
    {
        if (!$route) {
            $route = Route::currentRouteName();
        }
        $generator = new FormMailGenerator(['route' => $route]);
        return $generator->formName();
    }

    /**
     * @param $data
     * @return $this
     */
    public function head(array &$data) : FormMailHelper
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
    public function makeHead(array $data) : string
    {
        return Head::makeHead($data);
    }

    /**
     * @param array $data
     * @return array
     */
    public function languageInject(array $data) : array
    {
        $decorator = new FormMailDecorator(array_merge($data, [
            'data' => $data,
            'url' => \Config::get('app.url'),
            'recipient' => FormMailController::RECIPIENT,
            'sender' => FormMailController::SENDER,
            'time' => \Carbon\Carbon::now(),
            'formName' => $this->makeFormName()
        ]));
        return $decorator->resourceInject();
    }

    /**
     * @param array $data
     * @return $this
     */
    public function formName(array &$data) : FormMailHelper
    {
        if (array_key_exists('formName', $data)) {
            return $this;
        }
        $data['formName'] = $this->makeFormName();
        return $this;
    }

    /**
     * @param array $data
     * @param Request $request
     * @return $this
     */
    public function fields(array &$data, Request $request) : FormMailHelper
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
    public function makeFields(Request $request) : array
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
     * @param string $label
     * @param string $value
     * @param string $field
     * @return string[]
     */
    public function prepField(string $label, string $value, string $field) : array
    {
        return ['label' => $label, 'value' => $value, 'field' => $field];
    }

    /**
     * Set Message Subject
     *
     * @param array $data
     * @return $this
     */
    public function subject(array &$data) : FormMailHelper
    {
        if (array_key_exists('subject', $data)) {
            return $this;
        }

        $data['subject'] = $this->makeSubject($data);
        return $this;
    }

    /**
     * @param array $data
     * @return string
     */
    public function makeSubject(array $data = []) : string
    {
        // make form name if not already set
        $formName = array_key_exists('formName', $data) ? $data['formName'] : $this->makeFormName();

        // if subject lines for both recipient and sender are set then use them, otherwise resort to defaut
        $output = [];
        foreach ([FormMailController::RECIPIENT, FormMailController::SENDER] as $key) {
            // check if language key exists for this route to the recipient/sender key
            if (\Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key) !== self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key) {
                // get data to be injected into the language line.
                $inject = $this->languageInject(array_merge($data, ['formName' => $formName]));
                $output[$key] = \Lang::get(self::resourceRoot() . '.' . \Route::currentRouteName() . '.subject.' . $key,
                    $inject);
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
    public function premailer(Premailer $premailer, array $data) : array
    {
        // send out message to recipient
        // try and send the message with layout through Premailer
        $premailer = new FormMailPremailDecorator($premailer);
        return $premailer->premailer($data);
    }

    /**
     * @param array $data
     * @param null $value
     * @return $this
     */
    public function sender(array &$data, $value = null) : FormMailHelper
    {
        if (array_key_exists(FormMailController::SENDER, $data)) {
            return $this;
        }
        $data[FormMailController::SENDER] = $this->makeSender($data, $value);
        return $this;
    }

    /**
     * @param array $data
     * @param null $sender
     * @param null $url
     * @return string
     */
    public function makeSender(array $data, $sender = null, $url = null) : string
    {
        return Sender::makeSender($data, $sender, $url);
    }

    /**
     * @param array $data
     * @param string $form
     * @return $this
     */
    public function recipient(array &$data, string $form) : FormMailHelper
    {
        if (array_key_exists(FormMailController::RECIPIENT, $data)) {
            return $this;
        }
        $recipient = \Config::get('form_mail.recipient.' . $form);
        if ($recipient) {
            $data[FormMailController::RECIPIENT] = $recipient;
            return $this;
        }
        $data[FormMailController::RECIPIENT] = Recipient::makeRecipient($form);
        return $this;
    }

    /**
     * @param $form
     * @return static
     */
    public function makeRecipient(string $form, $url = null) : string
    {
        return Recipient::makeRecipient($form, $url);
    }

    /**
     * @param array $data
     * @return FormMail
     * @throws \Exception
     */
    public function makeMessage(array $data = []) : FormMail
    {
        return Message::makeMessage($data);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function confirmation(array &$data) : FormMailHelper
    {
        $key = strtolower(__FUNCTION__);
        if (array_key_exists($key, $data)) {
            return $this;
        }
        $data[$key] = Confirmation::get($data);
        return $this;
    }

    /**
     * Setup queue boolean field
     * @param $data
     * @return $this
     */
    public function queue(array &$data) : FormMailHelper
    {
        $key = strtolower(__FUNCTION__);
        if (array_key_exists($key, $data)) {
            return $this;
        }
        $data[$key] = Queue::get($data);
        return $this;
    }

    /**
     * Get custom request body that will be tacked onto return message
     * @param $data
     * @return $this
     */
    public function customRequestBody(array &$data) : FormMailHelper
    {
        $data['custom_request_body'] = \Request::instance()->query('customRequestBody');
        return $this;
    }
}
