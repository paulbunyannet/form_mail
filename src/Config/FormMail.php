<?php
return [
    'branding' => '',
    'rules' => [],
    'route_rules' => [
        'form-mail' => [
            'send' => []
        ],
    ],
    'queue' => true,
    'confirmation' => false,
    'delay' => [
        'send_message' => 15,
        'send_confirmation' => 15
    ]
];
