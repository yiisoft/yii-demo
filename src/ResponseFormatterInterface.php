<?php

namespace App;

use Psr\Http\Message\ResponseInterface;

interface ResponseFormatterInterface
{
    public function format(DeferredResponse $response): ResponseInterface;
}