<?php declare(strict_types=1);

namespace OAS\Resolver;

use OAS\Resolver\Factory\TreeFactory;
use OAS\Resolver\Graph\Node;
use OAS\Resolver\Graph\ReferenceNode;
use Psr\Http\Message\UriInterface;

final class Resolver
{
    private Configuration $configuration;

    private TreeFactory $treeFactory;

    /** @var array<string, Node> */
    private array $resolved = [];

    public function __construct(Configuration $configuration = null)
    {
        $this->configuration = $configuration ?? new Configuration();
        $this->treeFactory = new TreeFactory($this->configuration->getUriFactory());
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

    public function resolveDecoded($decoded, string $uri = null): Node
    {
        $uri = $this->createUri($uri ?? getcwd());
        $root = $this->treeFactory->create($decoded, $uri);
        $this->resolved[$uri->getPath()] = $root;

        return $this->doResolveRefs($root, [$root]);
    }

    public function resolveEncoded(string $encoded, string $uri = null): Node
    {
        $uri = $this->createUri($uri ?? getcwd());
        $root = $this->treeFactory->create(
            $this->decode(
                $encoded
            ),
            $uri
        );
        $this->resolved[$uri->getPath()] = $root;

        return $this->doResolveRefs($root, [$root]);
    }

    private function doResolve(UriInterface $uri, array $visited = []): Node
    {
        $graph = $this->treeFactory->create(
            $this->decode(
                $this->fetch($uri)
            ),
            $uri
        );

        if (hasFragment($uri)) {
            $graph = $graph->find(
                $uri->getFragment()
            );
        }
        $this->resolved[(string)$uri] = $graph;

        return $this->doResolveRefs($graph, $visited);
    }

    private function doResolveRefs(Node $graph, array $visited): Node
    {
        foreach ($graph as $node) {
            if ($node instanceof ReferenceNode) {
                $refUri = resolve(
                    $this->createUri(($ref = $node->ref()) == '#' ? '#/' : $ref),
                    $node->parent()->canonicalUri()
                );

                $resolved = !is_null($reference = $this->resolved($refUri));

                if ($resolved) {
                    $node->resolve($reference, false);
                } else {
                    try {
                        $node->resolve(
                            $this->doResolve($refUri, $visited)
                        );
                    } catch (DecodingException $decodingException) {
                        throw new UndecodeableRefException(
                            $node->parent()->uri(), $refUri, $node->ref(), $decodingException
                        );
                    } catch (FetchingException $fetchingException) {
                        throw new UnreachableRefException(
                            $node->parent()->uri(), $node->ref(), $fetchingException
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

        return $this->cache(
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

        return $this->cache(
            'decoded_' . md5($encoded),
            function () use ($decoder, $encoded) {
                return $decoder->decode($encoded);
            }
        );
    }

    private function cache(string $key, callable $getValue)
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

    private function resolved(UriInterface $uri): ?Node
    {
        $resolved = $this->resolved[$uri->getPath()] ?? null;
        if (null === $resolved) {

            return null;
        }

        return hasFragment($uri) ? $resolved->find($uri->getFragment()) : $resolved;
    }

    private function createUri(string $uri): UriInterface
    {
        return $this->configuration->getUriFactory()->createUri($uri);
    }
}
