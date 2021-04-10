<?php

declare(strict_types=1);

use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\UrlGeneratorInterface;

return [
    UrlGeneratorInterface::class => [
        'class' => UrlGenerator::class,
        'callMethods' => [
            'setEncodeRaw' => [$params['yiisoft/router-fastroute']['encodeRaw']],
        ],
    ],
];
