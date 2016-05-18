<?php

namespace Pbc\FormMail\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Pbc\Bandolier\Type\Strings;
use Pbc\FormMail\Helpers\FormMailHelper;
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
        $validator = \Validator::make($request->all(), $this->rules, []);
        if ($validator->fails()) {
            return \Response::json(['error' => $validator->errors()->all()]);
        }

        /** @var array $data List of data items that will be passed to views */
        $data = [];

        /** @var string $formName name of form from route name */
        $data['formName'] = $this->helper->formName();
        // create recipient from the form name and the current host
        $data['recipient'] = $this->helper->recipient(\Route::currentRouteName());
        /** @var string $resource path to recources, used for path to view and localization */
        $resource = str_replace('\\', '.', strtolower(__CLASS__)) . '.' . strtolower(__FUNCTION__);
        $data['resource'] = $resource;

        // create fields list from the fields submitted to this handler
        $data['fields'] = [];
        $this->helper->requestFields($request, $data);

        // headline for return response
        $data['head'] = \Lang::get('pbc_form_mail::body.'.\Route::currentRouteName(), [
            'form' => Strings::formatForTitle($data['formName']),
            'recipient' => $data['recipient'],
        ]);

        /** @var string $response response that will be passed as success */
        $data['response'] = \View::make('pbc_form_mail::body')->with('data', $data)->render();

        // email message subject
        $this->helper->subject($data);

        // headline for email message
        $data['head'] = \Lang::get('pbc_form_mail::body.recipient-success', [
            'form' => Strings::formatForTitle($data['formName']),
            'domain' => parse_url(\App::make('url')->to('/'), PHP_URL_HOST),
            'time' => Carbon::now()
        ]);

        // branding string
        $this->helper->branding($data);

        // body of email message
        $data['body'] = \View::make('pbc_form_mail::body')->with('data', $data)->render();

        $message = $this->helper->premailer($this->premailer, $data);

        // try and email out the message
        try {
            \Mail::send(
                'pbc_form_mail_template::body',
                ['data' => $message],
                function ($message) use ($data, $request) {
                    $message->to($data['recipient'])->subject($data['subject']);
                    $message->from($request->input('email'), $request->input('name'));
                }
            );
        } catch (\Exception $ex) {
            return \Response::json(['error' => [$ex->getMessage()]]);
        }

        // return the response message as a success
        return \Response::json(['success' => [$data['response']]]);
    }
}
