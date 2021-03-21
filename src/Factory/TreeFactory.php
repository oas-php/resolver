<?php declare(strict_types=1);

namespace OAS\Resolver\Factory;

use OAS\Resolver\Graph\Node;
use OAS\Resolver\Graph\ReferenceNode;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use function OAS\Resolver\encode;
use function OAS\Resolver\join;
use function OAS\Resolver\pathSegments;
use function OAS\Resolver\resolve;

class TreeFactory
{
    private UriFactoryInterface $uriFactory;

    public function __construct(UriFactoryInterface $uriFactory)
    {
        $this->uriFactory = $uriFactory;
    }

    public function create($tree, UriInterface $uri, UriInterface $canonicalUri = null): Node
    {
        $meta = [];
        $canonicalUri = $canonicalUri ?? $uri;

        if ($tree instanceof \stdClass) {
            $tree = (array) $tree;
            $meta['emptyObject'] = empty($value);
        }

        if (is_array($tree)) {
            if (array_key_exists('$id', $tree) && is_string($tree['$id'])) {
                $canonicalUri = resolve(
                    $this->uriFactory->createUri($tree['$id']),
                    $canonicalUri
                );
            }

            $node = new Node($uri, $canonicalUri, [], $meta);

            foreach ($tree as $path => $subTree) {
                $path = (string) $path;
                $encodedPath = encode($path);

                $node->add(
                    $path,
                    $this->ref($path, $subTree, $uri)
                            ? new ReferenceNode($subTree)
                            : $this->create(
                                $subTree,
                                $uri->withFragment(
                                    join($uri->getFragment(), $encodedPath)
                                ),
                                $canonicalUri->withFragment(
                                    join($canonicalUri->getFragment(), $encodedPath)
                                )
                            )
                );
            }
        } else {
            $node = new Node($uri, $canonicalUri, $tree, $meta);
        }

        return $node;
    }

    private function ref(string $path, $subTree, UriInterface $uri): bool
    {
        return '$ref' == $path && is_string($subTree) && $this->refContext($uri->getFragment());
    }

    private function refContext(string $path): bool
    {
        $pathSegments = pathSegments($path);

        if (!empty($pathSegments)) {
            // "properties" is immediate parent
            if ('properties' == $pathSegments[array_key_last($pathSegments)]) {
                return false;
            }

            // inside "enum"
            foreach ($pathSegments as $segment) {
                if ('enum' == $segment) {
                    return false;
                }
            }

            // TODO : what's more beside "const"?
        }

        return true;
    }
}
