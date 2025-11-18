<?php

namespace App\Mock\Symfony\HttpClient\ResponseFactory;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class NotImplementedMockResponseFactory implements MockResponseFactoryInterface
{
    private const int HTTP_NOT_IMPLEMENTED = 501;

    public function __invoke(string $method, string $url, array $options): ResponseInterface
    {
        return new MockResponse(body: sprintf('No Mock was found for path "%s" "%s".', $url, $method), info: [
            'http_code' => self::HTTP_NOT_IMPLEMENTED,
        ]);
    }
}
