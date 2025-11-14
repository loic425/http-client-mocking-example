<?php

namespace App\Mock\ResponseFactory;

use App\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsDecorator(MockResponseFactoryInterface::class)]
final class GetBookCollectionResponseFactory implements MockResponseFactoryInterface
{
    private const string URI_PATTERN = '#^.+/new$#';

    public function __construct(
        private readonly MockResponseFactoryInterface $responseFactory,
        #[Autowire('%kernel.project_dir%/src/Mock/Files')]
        private readonly string $mocksDir,
    ) {
    }

    public function __invoke(string $method, string $url, array $options): ResponseInterface
    {
        if (!preg_match(self::URI_PATTERN, $url)) {
            return ($this->responseFactory)($method, $url, $options);
        }

        return MockResponse::fromFile($this->mocksDir . '/new.json');
    }
}
