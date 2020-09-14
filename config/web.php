<?php

declare(strict_types=1);

use App\Blog\Comment\CommentRepository;
use App\Blog\Comment\CommentService;
use App\Contact\ContactMailer;
use App\Factory\MiddlewareDispatcherFactory;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Mailer\MailerInterface;
use Yiisoft\DataResponse\DataResponseFactory;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\DataResponse\DataResponseFormatterInterface;
use Yiisoft\DataResponse\Formatter\HtmlDataResponseFormatter;
use Yiisoft\Session\Session;
use Yiisoft\Session\SessionInterface;
use Yiisoft\Yii\Web\MiddlewareDispatcher;
use App\Repository\UserRepository;
use Yiisoft\Auth\AuthenticationMethodInterface;
use Yiisoft\Yii\Web\User\UserAuth;
use Yiisoft\Validator\ValidatorFactoryInterface;
use Yiisoft\Validator\ValidatorFactory;

/**
 * @var array $params
 */

return [
    // PSR-17 factories:
    RequestFactoryInterface::class => Psr17Factory::class,
    ServerRequestFactoryInterface::class => Psr17Factory::class,
    ResponseFactoryInterface::class => Psr17Factory::class,
    StreamFactoryInterface::class => Psr17Factory::class,
    UriFactoryInterface::class => Psr17Factory::class,
    UploadedFileFactoryInterface::class => Psr17Factory::class,
    DataResponseFormatterInterface::class => HtmlDataResponseFormatter::class,
    DataResponseFactoryInterface::class => DataResponseFactory::class,

    MiddlewareDispatcher::class => new MiddlewareDispatcherFactory(),
    SessionInterface::class => [
        '__class' => Session::class,
        '__construct()' => [
            $params['session']['options'] ?? [],
            $params['session']['handler'] ?? null,
        ],
    ],
    IdentityRepositoryInterface::class => UserRepository::class,
    AuthenticationMethodInterface::class => UserAuth::class,
    ContactMailer::class => static function (ContainerInterface $container) use ($params) {
        $mailer = $container->get(MailerInterface::class);
        return new ContactMailer($mailer, $params['supportEmail']);
    },
    CommentService::class => static function (ContainerInterface $container) {
        return new CommentService(
            $container->get(CommentRepository::class)
        );
    },
    ViewRenderer::class => [
        '__construct()' => [
            'viewBasePath' => '@views',
            'layout' => '@views/layout/main',
        ],
    ],
    ValidatorFactoryInterface::class => ValidatorFactory::class
];
