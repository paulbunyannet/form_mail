<?php

namespace Pbc\FormMail\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Pbc\Bandolier\Type\Encoded;
use Pbc\FormMail\Helpers\Confirmation;
use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\FormMail\Helpers\Queue;
use Pbc\FormMail\Traits\MessageTrait;
use Pbc\FormMail\Traits\QueueTrait;
use Pbc\FormMail\Traits\RulesTrait;
use Pbc\FormMail\Traits\SendTrait;
use Pbc\Premailer;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

/**
 * Class FormMailController
 * @package Pbc\FormMail\Http\Controllers
 */
class FormMailController extends \Illuminate\Routing\Controller
{
    use MessageTrait, QueueTrait, RulesTrait, SendTrait, ValidatesRequests, AuthorizesRequests, DispatchesJobs;

    /**
     * path to resources
     */
    const RESOURCE_ROOT = 'pbc_form_mail';

    /**
     * recipient key
     */
    const RECIPIENT = "recipient";

    /**
     * recipient value
     */
    const SENDER = "sender";

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
        $this->rules = $this->helper->prepRules();
    }

    /**
     * @param Request $request
     * @param array $data
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function requestHandler(Request $request, $data = [])
    {
        $return = [
            'queue' => Queue::getDefault(),
            'confirmation' => Confirmation::getDefault(),
        ];

        $validator = Validator::make(
            $request->all(),
            array_merge($this->rules,
                [
                    'email' => 'required|email',
                    'name' => 'required',
                    'fields' => 'required|array'
                ]
            ),
            [
                'required' => 'The :attribute field is required.',
                'email' => 'The :attribute must be a valid email address.',
                'array' => 'The :attribute must be an array.',
            ]
        );
        if ($validator->fails()) {
            $return['error'] = $validator->errors()->all();
            return Response::json($return);
        }
        
        $request->request->add(['time' => now()->toString()]);
        $fields = $request->get('fields');
        array_push($fields, 'time');
        $request->request->set('fields', $fields);

        // make form name
        $this->helper
            ->formName($data)

            // create recipient from the form name and the current host
            ->recipient($data, \Route::currentRouteName())

            // set sender key
            ->sender($data, $request->input('email'))

            // path to resources, used for path to view and localization
            ->resource($data, __CLASS__, __FUNCTION__)

            // create fields list from the fields submitted to this handler
            ->fields($data, $request)

            // Email message subject
            ->subject($data)

            // branding string
            ->branding($data)

            // headline for return response
            ->head($data)

            /** @var string $response response that will be passed as success */
            ->response($data)

            // setup confirmation boolean
            ->confirmation($data)

            // setup queue boolean field
            ->queue($data)

            // custom request data passed from elsewhere that will be padded into body
            ->customRequestBody($data);

        // make record in formMail model and put message in job queue
        try {
            $data['premailer'] = $this->premailer;
            \FormMailHelper::makeMessage($data);
            $return['success'] = [Encoded::getThingThatIsEncoded($data['response'], self::SENDER)];

        } catch (\Exception $ex) {
            $return['error'] = [$ex->getMessage(), $ex->getLine(), $ex->getFile()];
        }

        return Response::json($return);

    }
}
