<?php

namespace Pbc\FormMail\Http\Controllers;

use App\Http\Controllers\Controller;

use DB;
use Illuminate\Http\Request;
use Pbc\Bandolier\Type\Encoded;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\Confirmation;
use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\FormMail\Helpers\Queue;
use Pbc\FormMail\Traits\MessageTrait;
use Pbc\FormMail\Traits\QueueTrait;
use Pbc\FormMail\Traits\RulesTrait;
use Pbc\FormMail\Traits\SendTrait;
use Pbc\Premailer;
use Response;
use Validator;

/**
 * Class FormMailController
 * @package Pbc\FormMail\Http\Controllers
 */
class FormMailController extends Controller
{
    use MessageTrait, QueueTrait, RulesTrait, SendTrait;

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
    protected $rules = [
        'email' => 'required|email',
        'name' => 'required',
        'fields' => 'required|array'
    ];
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
        $this->rules = \FormMailHelper::prepRules();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestHandler(Request $request, $data = [])
    {
        $return = [
            'queue' => Queue::getDefault(),
            'confirmation' => Confirmation::getDefault(),
        ];

        $validator = Validator::make($request->all(), $this->rules, []);
        if ($validator->fails()) {
            $return['error'] = $validator->errors()->all();
            return Response::json($return);
        }

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
            ->queue($data);


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
