<?php

return [
    'isCrawler' => [
        'file' => 'iscrawler',
        'description' => 'Определяет, является ли текущий посетитель ботом (краулером). Возвращает 1 или 0; опционально заполняет плейсхолдер с именем бота.',
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
        'description' => 'PreHook для FormIt: блокирует отправку формы при обнаружении бота, выводит настраиваемое сообщение.',
        'properties' => [],
    ],
];
