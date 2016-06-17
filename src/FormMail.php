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
    public function setMessageAttribute($value)
    {
        if (is_string($value)) {
            $value = ['html' => $value];
        }
        $this->attributes['message'] = json_encode($value);
    }

    /**
     * @return array
     */
    public function getMessageAttribute()
    {
        return json_decode($this->attributes['message'], true);
    }
}
