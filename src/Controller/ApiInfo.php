<?php

namespace App\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Yii\Web\WebResponseFactoryInterface;

class ApiInfo implements MiddlewareInterface
{
    private WebResponseFactoryInterface $responseFactory;

    public function __construct(WebResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->responseFactory->createResponse(200, '', ['version' => '2.0', 'author' => 'yiisoft']);
    }
}
