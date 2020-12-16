<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;
use OAS\Resolver as UriHelpers;

final class Resolver
{
    private $configuration;

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?? new Configuration();
    }

    public function configuration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param UriInterface|string $uri
     * @return Reference|array
     */
    public function resolve($uri)
    {
        return $this->doResolve(
            $this->createUri($uri), []
        );
    }

    /**
     * @param UriInterface $uri
     * @param array $resolvedRefs
     * @return Reference|array
     */
    private function doResolve(UriInterface $uri, array $resolvedRefs)
    {
        $decoded = $this->decode(
            $this->fetch($uri)
        );

        if (hasFragment($uri)) {
            $decoded = retrieveByPath(
                $decoded, pathSegments($uri->getFragment())
            );
        }

        foreach ($this->walk($decoded) as $path => $ref) {
            $resolvedRef = $this->resolveUri(
                $this->createUri($ref), $uri
            );

            // is recursion detected?
            $ref = \array_key_exists((string) $resolvedRef, $resolvedRefs)
                ?
                    $resolvedRefs[(string) $resolvedRef]
                :
                    Reference::createDeferred(
                        $ref,
                        function (Reference $reference) use ($resolvedRef, $resolvedRefs, $uri) {
                            $resolvedRefs[(string) $resolvedRef] = $reference;

                            return $this->doResolve(
                                $resolvedRef, $resolvedRefs
                            );
                        }
                    );

            $decoded = replaceAtPath($decoded, $path, $ref);
        }

        return $decoded;
    }

    private function fetch(UriInterface $uri): string
    {
        $normalized = (string) UriHelpers\realPath(
            UriHelpers\withoutFragment($uri)
        );

        return $this->fromCache(
            'fetched_' . md5($normalized),
            function () use ($normalized, $uri) {
                $raw = @file_get_contents(urldecode($normalized));

                if (false === $raw) {
                    throw new FetchingException($uri, error_get_last()['message']);
                }

                return $raw;
            }
        );
    }

    private function decode(string $encoded): array
    {
        $decoder = $this->configuration->getDecoder();

        return $this->fromCache(
            'decode_' . md5($encoded),
            function () use ($decoder, $encoded) {
                return $decoder->decode($encoded);
            }
        );
    }

    private function resolveUri(UriInterface $ref, UriInterface $context): UriInterface
    {
        $resolved = $ref->withPath(
            realPath(
                $ref,
                isAbsolute($ref) ? null
                    : (hasFragment($ref) && !hasPath($ref) ? $context : UriHelpers\dirname($context))
            )
            ->getPath()
        );

        $resolved = hasHost($resolved)
            ? $resolved->withHost(
                $context->getHost()
            )
            : $resolved;

        return hasScheme($resolved)
            ? $resolved->withScheme(
                $context->getScheme()
            )
            : $resolved;
    }

    /**
     * @param UriInterface|string $uri
     * @return UriInterface
     */
    private function createUri($uri): UriInterface
    {
        if (is_string($uri)) {
            $uri = $this->configuration->getUriFactory()->createUri($uri);
        }

        if (!$uri instanceof UriInterface) {
            throw new \InvalidArgumentException('Uri must be of string or \Psr\Http\Message\UriInterface type');
        }

        return $uri;
    }

    private function fromCache(string $key, callable $getValue)
    {
        $cache = $this->configuration->getCache();

        if (!is_null($cache)) {
            if (!$cache->has($key)) {
                $cache->set($key, call_user_func($getValue));
            }

            $value = $cache->get($key);
        } else {
            $value = call_user_func($getValue);
        }

        return $value;
    }

    private function walk(array $graph, array $path = []): \Generator
    {
        foreach ($graph as $pathSegment => $node) {
            $currentPath = $path;

            if ('$ref' === $pathSegment) {
                if (!\is_string($node)) {
                    throw new \LogicException('$ref must be of string type');
                }

                yield $path => $node;
                continue;
            }

            if (is_array($node)) {
                yield from $this->walk($node, append($currentPath, $pathSegment));
            }
        }
    }
}
