<?php declare(strict_types=1);

namespace OAS\Resolver\Factory;

use Psr\SimpleCache\CacheInterface;

final class CacheFactory
{
    public static function create(): CacheInterface
    {
        if (!class_exists('\Cache\Adapter\PHPArray\ArrayCachePool')) {
            throw new \RuntimeException(
                sprintf(
                    'Install cache/array-adapter package (composer req cache/array-adapter) or provide implementation of %s',
                    CacheInterface::class
                )
            );
        }

        return new \Cache\Adapter\PHPArray\ArrayCachePool();
    }
}
