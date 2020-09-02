<?php

use App\Factory\AppRouterFactory;
use App\Factory\MailerFactory;
use App\Timer;
use Psr\Container\ContainerInterface;
use Yiisoft\Access\AccessCheckerInterface;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\Rbac\Manager;
use Yiisoft\Rbac\Php\Storage;
use Yiisoft\Rbac\RuleFactory\ClassNameRuleFactory;
use Yiisoft\Rbac\RuleFactoryInterface;
use Yiisoft\Rbac\StorageInterface;
use Yiisoft\Router\FastRoute\UrlGenerator;
use Yiisoft\Router\Group;
use Yiisoft\Router\RouteCollectorInterface;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Router\UrlMatcherInterface;

/**
 * @var array $params
 */

$timer = new Timer();
$timer->start('overall');

return [
    ContainerInterface::class => static function (ContainerInterface $container) {
        return $container;
    },

    //mail
    Swift_Transport::class => Swift_SmtpTransport::class,
    Swift_SmtpTransport::class => [
        '__class' => Swift_SmtpTransport::class,
        '__construct()' => [
            'host' => $params['mailer']['host'],
            'port' => $params['mailer']['port'],
            'encryption' => $params['mailer']['encryption'],
        ],
        'setUsername()' => [$params['mailer']['username']],
        'setPassword()' => [$params['mailer']['password']],
    ],

    // Router:
    RouteCollectorInterface::class => Group::create(),
    UrlMatcherInterface::class => new AppRouterFactory(),
    UrlGeneratorInterface::class => UrlGenerator::class,

    MailerInterface::class => new MailerFactory($params['mailer']['writeToFiles']),
    Timer::class => $timer,

    StorageInterface::class => [
        '__class' => Storage::class,
        '__construct()' => [
            'directory' => $params['aliases']['@root'] . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'rbac'
        ]
    ],
    RuleFactoryInterface::class => ClassNameRuleFactory::class,
    Manager::class => static function (ContainerInterface $container) {
        $storage = $container->get(StorageInterface::class);
        $ruleFactory = $container->get(RuleFactoryInterface::class);
        return new Manager($storage, $ruleFactory);
    },
    AccessCheckerInterface::class => Manager::class,
];
