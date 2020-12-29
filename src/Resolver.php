<?php declare(strict_types=1);

namespace OAS\Resolver;

use OAS\Resolver\Graph\Node;
use OAS\Resolver\Graph\ReferenceNode;
use Psr\Http\Message\UriInterface;

final class Resolver
{
    private Configuration $configuration;

    /** @var Node[] */
    private array $resolved;

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?? new Configuration();
    }

    public function configuration(): Configuration
    {
        return $this->configuration;
    }

    public function resolve(string $uri): Node
    {
        $this->resolved = [];

        return $this->doResolve(
            $this->createUri($uri)
        );
    }

    public function resolveEncoded(string $encoded, string $uri = null): Node
    {
        $uri = $this->createUri($uri ?? getcwd());
        $root = new Node($uri, $this->decode($encoded));
        $this->resolved = [$root];

        return $this->doResolveRefs($root, $uri, $this->resolved);
    }

    public function resolveDecoded($decoded, string $uri = null): Node
    {
        $uri = $this->createUri($uri ?? getcwd());
        $root = new Node($uri, $decoded);
        $this->resolved = [$root];

        return $this->doResolveRefs($root, $uri, $this->resolved);
    }

    private function doResolve(UriInterface $uri, array $visited = []): Node
    {
        $graph = $this->resolved[] = new Node(
            $uri, $this->decode(
                $this->fetch($uri)
            )
        );

        if (hasFragment($uri)) {
            $graph = $graph->find(
                $uri->getFragment()
            );
        }

        return $this->doResolveRefs($graph, $uri, $visited);
    }

    private function doResolveRefs(Node $graph, UriInterface $uri, array $visited, bool $changeBaseUri = true): Node
    {
        $baseUri = null;

        /** @var Node $node */
        foreach ($graph as $node) {
            if ($changeBaseUri && '$id' === $node->pathFromParent() && $node->isLeaf()) {
                $baseUri = $this->createUri(
                    $node->value()
                );
            }

            if ($node instanceof ReferenceNode) {
                if ($changeBaseUri && !$node->isRoot() && $node->parent()->has('$id')) {
                    $idNode = $node->get('$id');
                     $baseUri = $idNode->isLeaf()
                         ? $this->createUri(
                             $idNode->value()
                         )
                         : $baseUri;
                }

                $ref = $node->ref();
                $refUri = $this->resolveUri(
                    $this->createUri($ref == '#' ? '#/' : $ref), $uri, $baseUri
                );

                $isResolved = !is_null($reference = $this->resolved($refUri));

                if ($isResolved) {
                    $node->resolve($reference, false);
                } else {
                    try {
                        $node->resolve(
                            $this->doResolve($refUri, $visited)
                        );
                    } catch (DecodingException $decodingException) {
                        throw new UndecodeableRefException(
                            $uri, $refUri, $node->ref(), $decodingException
                        );
                    } catch (FetchingException $fetchingException) {
                        throw new UnreachableRefException(
                            $uri, $node->ref(), $fetchingException
                        );
                    }
                }
            } else {
                $visited[] = $node;
            }
        }

        return $graph;
    }

    private function fetch(UriInterface $uri): string
    {
        $normalized = (string) realPath(
            $uri->withFragment('')
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

    private function decode(string $encoded)
    {
        $decoder = $this->configuration->getDecoder();

        return $this->fromCache(
            'decoded_' . md5($encoded),
            function () use ($decoder, $encoded) {
                return $decoder->decode($encoded);
            }
        );
    }

    private function createUri(string $uri): UriInterface
    {
        return $this->configuration->getUriFactory()->createUri($uri);
    }

    private function resolveUri(UriInterface $ref, UriInterface $context, UriInterface $baseUri = null): UriInterface
    {
        if (!is_null($baseUri)) {
            $context = $this->resolveUri($baseUri, $context);
        }

        $resolved = $ref->withPath(
            realPath(
                $ref,
                isAbsolute($ref) ? null
                    : (hasFragment($ref) && !hasPath($ref) ? $context : dirname($context))
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

    private function resolved(UriInterface $uri): ?Node
    {
        foreach ($this->resolved as $resolvedNode) {
            if (includes($resolvedNode->uri(), $uri)) {
                return $resolvedNode->find(
                    $uri->getFragment()
                );
            }
        }

        return null;
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
}
