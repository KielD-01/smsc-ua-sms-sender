<?php

/**
 * Rules file for sending SMS/MMS etc.
 */

return [
    'sms' => [
        'allowed_arguments' => [
            'id', 'translit', 'sender', 'tinyurl', 'time', 'tz',
            'period', 'freq', 'flash', 'bin', 'push', 'hlr',
            'ping', 'mms', 'mail', 'viber', 'call', 'voice',
            'param', 'subj', 'charset', 'cost', 'fmt', 'list',
            'valid', 'maxsms', 'imgcode', 'userip', 'err', 'op',
            'pp', 'login', 'psw'
        ]
    ],
    'mms' => [
        'allowed_arguments' => [
            'login', 'psw'
        ]
    ],
    'email' => [
        'allowed_arguments' => [
            'login', 'psw'
        ]
    ],
    'voice' => [
        'allowed_arguments' => [
            'login', 'psw'
        ]
    ],
    'viber' => [
        'allowed_arguments' => [
            'login', 'psw'
        ]
    ]
];