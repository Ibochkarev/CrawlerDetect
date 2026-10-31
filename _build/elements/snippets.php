<?php

return [
    'isCrawler' => [
        'file' => 'iscrawler',
        'description' => 'Detects if the current visitor is a bot (crawler). Returns 1 or 0; optionally fills a placeholder with the bot name.',
        'properties' => [
            'userAgent' => [
                'type' => 'textfield',
                'value' => '',
            ],
            'placeholderPrefix' => [
                'type' => 'textfield',
                'value' => 'crawlerdetect.',
            ],
        ],
    ],
    'crawlerDetectBlock' => [
        'file' => 'crawlerdetectblock',
        'description' => 'PreHook for FormIt: blocks form submission when a bot is detected, displays a customizable message.',
        'properties' => [],
    ],
];