<?php

namespace Pbc\FormMail\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Pbc\Bandolier\Type\Encoded;
use Pbc\FormMail\FormMail;
use Pbc\FormMail\Helpers\FormMailHelper;
use Pbc\FormMail\Traits\QueueTrait;
use Pbc\FormMail\Traits\RulesTrait;
use Pbc\FormMail\Traits\SendTrait;
use Pbc\FormMail\Traits\MessageTrait;
use Pbc\Premailer;

/**
 * Class FormMailController
 * @package Pbc\FormMail\Http\Controllers
 */
class FormMailController extends Controller
{
    use QueueTrait, SendTrait, MessageTrait, RulesTrait;
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
        $this->prepRules();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestHandler(Request $request, $data = [])
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
            ->response($data);


        // make record in formMail model
        \DB::beginTransaction();
        try {
            $formMailModelData = [
                'form' => $data['formName'],
                'resource' => $data['resource'],
                'sender' => $data['sender'],
                'recipient' => $data['recipient'],
                'fields' => $data['fields'],
                'subject' => $data['subject'],
                'branding' => $data['branding'],
                'message_sent_to_recipient' => false,
                'confirmation_sent_to_sender' => false,
            ];
            $formMailModel = new FormMail($formMailModelData);
            $formMailModel->save();
            $this->messageToRecipient($formMailModel);
            $this->messageToSender($formMailModel);
        } catch (\Exception $ex) {
            // @codeCoverageIgnoreStart
            \DB::rollBack();
            $return['error'] = [$ex->getMessage()];
            return \Response::json($return);
            // @codeCoverageIgnoreEnd
        }
        \DB::commit();
        // if we should be queueing this message and confirmation,
        // then do that here, otherwise email out the messages
        // below.
        try {
            if (config('form_mail.queue')) {
                $this->queue($formMailModel);
            } else {
                $this->send($formMailModel);
            }
            // return the response message as a success
            $return['success'] = [Encoded::getThingThatIsEncoded($data['response'], FormMailHelper::SENDER)];
        } catch (\Exception $ex) {
            $return['error'] = [$ex->getMessage()];
        }

        return \Response::json($return);

    }

}
