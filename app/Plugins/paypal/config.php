<?php

return [

    'paypal' => [
        'mode'      => env('PAYPAL_MODE', 'sandbox'),
        'username'  => env('PAYPAL_USERNAME', ''),
        'password'  => env('PAYPAL_PASSWORD', ''),
        'signature' => env('PAYPAL_SIGNATURE', ''),
    ],

];
