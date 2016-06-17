<?php

namespace Pbc\FormMail\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Pbc\Bandolier\Type\Strings;
use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\FormMail\Jobs\FormMailSendConfirmationMessage;
use Pbc\FormMail\Jobs\FormMailSendMessage;
use Pbc\Premailer;

/**
 * Class FormMailController
 * @package Pbc\FormMail\Http\Controllers
 */
class FormMailController extends Controller
{

    /**
     * @var array
     */
    protected $rules = [];
    /**
     * @var Premailer
     */
    protected $premailer;

    /**
     * FormMailController constructor.
     * @param Premailer $premailer
     */
    public function __construct(Premailer $premailer, FormMailHelper $helper)
    {
        $this->premailer = $premailer;
        $this->helper = $helper;
        $this->rules = \Config::get('form_mail.rules');

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestHandler(Request $request)
    {
        $return = [
            'queue' => config('form_mail.queue'),
            'confirmation' => config('form_mail.confirmation'),
        ];
        
        $validator = \Validator::make($request->all(), $this->rules, []);
        if ($validator->fails()) {
            $return['error'] = $validator->errors()->all();
            return \Response::json($return);
        }

        /** @var array $data List of data items that will be passed to views */
        $data = [];

        /** @var string $form name of form from route name */
        $data['formName'] = $this->helper->formName();
        
        // create recipient from the form name and the current host
        $data['recipient'] = $this->helper
            ->recipient(\Route::currentRouteName());
        /** @var string $resource path to recources, used for path to view and localization */
        $resource = str_replace('\\', '.', strtolower(__CLASS__)) . '.' . strtolower(__FUNCTION__);
        $data['resource'] = $resource;

        // create fields list from the fields submitted to this handler
        $data['fields'] = [];
        $this->helper->requestFields($request, $data);

        // headline for return response
        $data['head'] = \Lang::get(
            'pbc_form_mail::body.' . \Route::currentRouteName(),
            [
                'form' => Strings::formatForTitle($data['formName']),
                'recipient' => $data['recipient'],
            ]
        );

        /** @var string $response response that will be passed as success */
        $data['response'] = \View::make('pbc_form_mail::body')
            ->with('data', $data)
            ->render();

        // email message subject
        $this->helper->subject($data);

        // headline for email message
        $data['head'] = \Lang::get(
            'pbc_form_mail::body.recipient-success',
            [
                'form' => Strings::formatForTitle($data['formName']),
                'domain' => parse_url(
                    \App::make('url')->to('/'),
                    PHP_URL_HOST
                ),
                'time' => Carbon::now()
            ]
        );

        // branding string
        $this->helper->branding($data);

        // body of email message
        $data['body'] = \View::make('pbc_form_mail::body')
            ->with('data', $data)
            ->render();

        $message = $this->helper->premailer($this->premailer, $data);

        // make record in formMail model
        
        $formMailModel = new \Pbc\FormMail\FormMail();
        $formMailModel->form = $data['formName'];
        $formMailModel->resource = $data['resource'];
        $formMailModel->sender = $request->input('email');
        $formMailModel->recipient = $data['recipient'];
        $formMailModel->fields = $data['fields'];
        $formMailModel->message = $message;
        $formMailModel->subject = $data['subject'];
        $formMailModel->message_sent_to_recipient = false;
        $formMailModel->confirmation_sent_to_sender = false;
        $formMailModel->save();

        // if we should be queueing this message and confirmation,
        // then do that here, otherwise email out the messages
        // below.
        try {
            if (config('form_mail.queue', false) == true) {
                $this->queue($formMailModel);
            } else {
                $this->send($formMailModel);
            }
            // return the response message as a success
            $return['success'] = [$data['response']];
        } catch (\Exception $ex) {
            $return['error'] = [$ex->getMessage()];
        }

        return \Response::json($return);

    }

    /**
     * Send messages out to recipients
     * 
     * @param \Pbc\FormMail\FormMail $formMailModel
     * @throws \Exception
     */
    private function send(\Pbc\FormMail\FormMail $formMailModel)
    {

        // try and email out the message to the recipient.
        // If it fails then return the exception as the
        // response.
        try {
            \Mail::send(
                'pbc_form_mail_template::body',
                ['data' => $formMailModel->message],
                function ($message) use ($formMailModel) {
                    $message->to($formMailModel->recipient)
                        ->subject($formMailModel->subject)
                        ->from(
                            $formMailModel->sender
                        );
                }
            );
            $formMailModel->message_sent_to_recipient = true;
            $formMailModel->save();
        } catch (\Exception $ex) {
            throw new \Exception($ex->getMessage());
        }

        if (\Config::get('form_mail.confirmation')) {
            // try and send out message to sender for conformation.
            // If it fails then return the exception as the
            // response.
            try {
                $data = $formMailModel->toArray();
                $data['head'] = \Lang::get(
                    'pbc_form_mail::body.success',
                    [
                        'form' => Strings::formatForTitle($formMailModel->form),
                        'recipient' => $formMailModel->recipient,
                    ]
                );
                $data['body'] = \View::make('pbc_form_mail::body')
                    ->with('data', $data)
                    ->render();
                $message = $this->helper->premailer(
                    $this->premailer,
                    $data
                );
                \Mail::send(
                    'pbc_form_mail_template::body',
                    ['data' => $message],
                    function ($message) use ($formMailModel) {
                        $message->to($formMailModel->sender)
                            ->subject($formMailModel->subject)
                            ->from($formMailModel->recipient);
                    }
                );
                $formMailModel->confirmation_sent_to_sender = true;
                $formMailModel->save();
            } catch (\Exception $ex) {
                throw new \Exception($ex->getMessage());
            }
        }
    }
    

    /**
     * Queue the messages for sending on next queue process
     * 
     * @param \Pbc\FormMail\FormMail $formMailModel
     */
    private function queue(\Pbc\FormMail\FormMail $formMailModel)
    {
        $this->dispatch(
            new FormMailSendMessage($formMailModel)
        );
        if (config('form_mail.confirmation', false) == true) {
            $this->dispatch(
                new FormMailSendConfirmationMessage(
                    $formMailModel,
                    $this->premailer,
                    $this->helper
                )
            );
        }
    }
}
