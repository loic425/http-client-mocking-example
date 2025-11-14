<?php

declare(strict_types=1);

namespace App\Tests\Unit\Symfony;

use App\Symfony\HttpClient\ResponseFactory\MockResponseFactoryInterface;
use App\Symfony\HttpClient\ResponseFactory\NotImplementedMockResponseFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;

final class NotImplementedMockResponseFactoryTest extends TestCase
{
    public function testImplementsMockFactoryInterface(): void
    {
        $this->assertInstanceOf(MockResponseFactoryInterface::class, new NotImplementedMockResponseFactory());
    }

    public function testResponseHasNotImplementedAsStatusCode(): void
    {
        $response = (new NotImplementedMockResponseFactory())->__invoke('GET', '/path', []);

        $this->assertEquals(501, $response->getStatusCode());
    }

    public function testResponseHasMessageOnContent(): void
    {
        $client = new MockHttpClient(new NotImplementedMockResponseFactory());
        $response = $client->request('GET', '/path', []);

        $this->assertEquals('No Mock was found for path "https://example.com/path" "GET".', $response->getContent(false));
    }
}
