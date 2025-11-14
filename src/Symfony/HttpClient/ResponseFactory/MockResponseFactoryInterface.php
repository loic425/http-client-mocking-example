<?php

declare(strict_types=1);

namespace App\Symfony\HttpClient\ResponseFactory;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface MockResponseFactoryInterface
{
    public function __invoke(string $method, string $url, array $options): ResponseInterface;
}
