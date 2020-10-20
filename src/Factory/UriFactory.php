<?php declare(strict_types=1);

namespace OAS\Resolver\Factory;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class UriFactory implements UriFactoryInterface
{
    public function __construct()
    {
        if (!class_exists(\GuzzleHttp\Psr7\Uri::class)) {
            throw new \RuntimeException(
                sprintf('URI Factory not provided. Install guzzlehttp/psr7 package or provide implementation of %s ', UriFactoryInterface::class)
            );
        }
    }

    public function createUri(string $uri = ''): UriInterface
    {
        return new \GuzzleHttp\Psr7\Uri($uri);
    }
}
