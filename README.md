## Symfony Http Client Mocking

This project is an example to mock http client responses.

### Test it

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

### Example

*Get new books*

```php
<?php

namespace App\Mock\ResponseFactory;

use App\Mock\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface;use Symfony\Component\DependencyInjection\Attribute\AsDecorator;use Symfony\Component\DependencyInjection\Attribute\Autowire;use Symfony\Component\HttpClient\Response\MockResponse;use Symfony\Contracts\HttpClient\ResponseInterface;

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

To configure the mock services:

```yaml
# services_test.yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Mock\:
        resource: '../src/Mock'

    App\Mock\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface:
        alias: App\Mock\Symfony\HttpClient\ResponseFactory\NotImplementedMockResponseFactory

    app.symfony.mock.client:
        class: Symfony\Component\HttpClient\MockHttpClient
        arguments:
            - '@App\Mock\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface'

    # Replace book client with the mock one
    book.client:
        alias: app.symfony.mock.client
```

Of course, you will need to configure these services for non-production envs only.
