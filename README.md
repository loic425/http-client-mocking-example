## Symfony Http Client Mocking Example

This repository is a **small example project** demonstrating how to mock Symfony’s `HttpClient` using `MockHttpClient` and `MockResponse`.

The goal is to showcase a simple and structured approach to:

- Testing code that depends on an external API
- Simulating different HTTP responses
- Organizing mocks cleanly using response factories

## 🎯 Why This Repository Exists

When working with Symfony’s `HttpClient`, you often need to:

- Test without calling a real external API
- Simulate HTTP errors
- Precisely control the returned responses

This project demonstrates a clean way to achieve that.

## 🚀 Installation

```bash
git clone https://github.com/loic425/http-client-mocking-example.git
cd http-client-mocking-example
composer install
```

## ▶️ Test it

*with real API*
```shell
symfony console app:book-client
symfony console app:book-client /books/9781804617007
```

*with Mocks*
```shell
symfony console --env=test app:book-client
symfony console --env=test app:book-client /books/9781484206485
```

## 🕮 Example

*Get new books*

```php
<?php

namespace App\Mock\ResponseFactory;

use App\Mock\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface;
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
```

*Get specific book*

```php
<?php

namespace App\Mock\ResponseFactory;

use App\Mock\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface;
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
```

To use the mock client in the API:

```yaml
# services_test.yaml
services:
    # Replace book client with the mock one
    app.symfony.mock_http_client.book:
        class: Symfony\Component\HttpClient\MockHttpClient
        decorates: book.client
        arguments:
            - '@App\Mock\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface'
```

Of course, you will need to create this decoration for non-production envs only.

## ⚙️ How does it work?


### Create the MockResponseFactoryInterface

The interface is very simple.

```php
<?php

declare(strict_types=1);

namespace App\Mock\Symfony\HttpClient\ResponseFactory;

use Symfony\Contracts\HttpClient\ResponseInterface;

interface MockResponseFactoryInterface
{
    public function __invoke(string $method, string $url, array $options): ResponseInterface;
}
```

it's very close to the `Symfony\Contracts\HttpClient\HttpClientInterface` request method.

The first argument of the MockHttpClient is the response factory, and it accepts a callable.
So we just need to create an object which implements our interface to create this callable.

### Create the first implementation for the fallback

We alias the interface on the Symfony dependency injection system with our first Mock.

```php
<?php

namespace App\Mock\Symfony\HttpClient\ResponseFactory;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[AsAlias(MockResponseFactoryInterface::class)]
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
```

And then we'll be able to decorate the interface using the `AsDecorator` attribute from Symfony.
We are now able to implement our custom logic in each decorator using filesystem, or whatever.
