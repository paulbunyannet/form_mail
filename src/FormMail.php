<?php

namespace Pbc\FormMail;

use Illuminate\Database\Eloquent\Model;

/**
 * Class FormMail
 * @package Pbc\FormMail
 */
class FormMail extends Model
{
    protected $fillable = [
        'form',
        'resource',
        'sender',
        'recipient',
        'fields',
        'message',
        'subject',
        'branding',
        'message_sent_to_recipient',
        'confirmation_sent_to_sender',
    ];
    
    /**
     * @var string
     */
    protected $table = 'form_mail';

    /**
     * store the fields value as a json array
     *
     * @param $value
     */
    public function setFieldsAttribute($value)
    {
        $this->attributes['fields'] = json_encode($value);
    }

    /**
     * return the fields list as an array
     *
     * @return array
     */
    public function getFieldsAttribute()
    {
        return json_decode($this->attributes['fields'], true);
    }

    /**
     * @param $value
     * @return string
     */
    public function setMessageToRecipientAttribute($value)
    {
        if (is_string($value)) {
            $value = ['html' => $value];
        }
        $this->attributes['message_to_recipient'] = json_encode($value);
    }

    /**
     * @return array
     */
    public function getMessageToRecipientAttribute()
    {
        return json_decode($this->attributes['message_to_recipient'], true);
    }
    /**
     * @param $value
     * @return string
     */
    public function setMessageToSenderAttribute($value)
    {
        if (is_string($value)) {
            $value = ['html' => $value];
        }
        $this->attributes['message_to_sender'] = json_encode($value);
    }

    /**
     * @return array
     */
    public function getMessageToSenderAttribute()
    {
        return json_decode($this->attributes['message_to_sender'], true);
    }
}
