<?php

return [
    'mjml' => [
        'mjmlServiceUrl' => 'https://mjml.io',
        'timeout' => 10, // 0 is unlimited, int seconds
        'connectTimeout' => 1.5,
        'transportAdapter' => [
            'type' => 'sendmail',
            'options' => [
            ],
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'Zend\Mail\Transport\Sendmail' => 'Zend\Mail\Transport\Sendmail',
        ],
        'factories' => [
            'Client\Mjml' => 'ZendMjml\Factory\MjmlClientFactory',
            'Service\Mjml' => 'ZendMjml\Factory\MjmlFactory',
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
