<?php

namespace App\Mock\ResponseFactory;

use App\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsDecorator(MockResponseFactoryInterface::class)]
final class GetBookItemResponseFactory implements MockResponseFactoryInterface
{
    private const string URI_PATTERN = '#^.+/books/([^/]+)$#';

    public function __construct(
        private readonly MockResponseFactoryInterface $responseFactory,
        #[Autowire('%kernel.project_dir%/src/Mock/Files')]
        private readonly string $mocksDir,
    ) {
    }

    public function __invoke(string $method, string $url, array $options): ResponseInterface
    {
        if (!preg_match(self::URI_PATTERN, $url, $matches)) {
            return ($this->responseFactory)($method, $url, $options);
        }

        $isbn = $matches[1];

        $file = $this->mocksDir . '/books/' . $isbn . '.json';

        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('File "%s" does not exist', $file));
        }

        return MockResponse::fromFile($this->mocksDir . '/books/' . $isbn . '.json');
    }
}
