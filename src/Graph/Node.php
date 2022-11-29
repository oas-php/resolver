<?php declare(strict_types=1);

namespace OAS\Resolver\Graph;

use OAS\Resolver\JsonPointerException;
use Psr\Http\Message\UriInterface;
use function OAS\Resolver\{decode, encode, pathSegments};
use function iter\reduce;

/**
 * @template-extends \ArrayAccess<Node>
 * @template-extends \IteratorAggregate<Node>
 */
class Node implements \ArrayAccess, \JsonSerializable, \IteratorAggregate
{
    protected UriInterface $uri;

    protected UriInterface $canonicalUri;

    protected ?Node $parent = null;

    /** @var Node[]|scalar  */
    protected $value;

    /** @var ReferenceNode[]  */
    protected array $references;

    protected array $meta;

    public function __construct(UriInterface $uri, UriInterface $canonicalUri, $value = [], $meta = [])
    {
        $this->uri = $uri;
        $this->canonicalUri = $canonicalUri;
        $this->value = $value;
        $this->meta = $meta;
    }

    public function uri(): UriInterface
    {
        return $this->uri;
    }

    public function canonicalUri(): UriInterface
    {
        return $this->canonicalUri;
    }

    /**
     * @return Node[]|scalar
     */
    public function value()
    {
        return $this->value;
    }

    public function parent(): ?Node
    {
        $parent = $this->parent;

        while ($parent instanceof ReferenceNode) {
            $parent = $parent->parent;
        }

        return $parent;
    }

    public function has(string $path): bool
    {
        $value = $this->value();

        return is_array($value) && array_key_exists($path, $value);
    }

    public function get(string $path): ?Node
    {
        $nodes = $this->value();

        return $nodes[$path];
    }

    public function add(string $path, Node $node): void
    {
        $node->parent = $this;
        $this->value[$path] = $node;
    }

    public function path(): string
    {
        if ($this->isRoot()) {
            return '/';
        }

        return reduce(
            function ($path, Node $node) {
                return encode($node->pathFromParent()) . '/' . $path;
            },
            $this->nodesToRoot(),
            encode(
                $this->pathFromParent()
            )
        );
    }

    public function pathFromRoot(): string
    {
        return $this->path();
    }

    public function pathFromParent(): string
    {
        if ($this->isRoot()) {
            return '';
        }

        foreach ($this->parent()->value() as $path => $node) {
            if ($node === $this || ($this->parent instanceof ReferenceNode && $node === $this->parent)) {
                return (string) $path;
            }
        }

        throw new \LogicException('This should never happen!');
    }

    public function find(string $jsonPointer): Node
    {
        $currentNode = $this;
        $pathSegments = pathSegments($jsonPointer);

        while (!empty($pathSegments)) {
            $pathSegment = decode(
                array_shift($pathSegments)
            );

            switch ($pathSegment) {
                case '.':
                    break;

                case '..':
                    if ($currentNode->isRoot()) {
                        throw new JsonPointerException($jsonPointer);
                    }

                    $currentNode = $currentNode->parent;
                    break;

                default:
                    if (!$currentNode->has($pathSegment)) {
                        throw new JsonPointerException($jsonPointer);
                    }

                    $currentNode = $currentNode->get($pathSegment);
                    break;
            }
        }

        return $currentNode;
    }

    public function root(): Node
    {
        $node = $this;

        while (!$node->isRoot()) {
            $node = $node->parent;
        }

        return $node;
    }

    public function isRoot(): bool
    {
        return is_null($this->parent);
    }

    public function denormalize(bool $merge = false)
    {
        return $this->doDenormalize($merge, []);
    }

    protected function doDenormalize(bool $merge, array $visited)
    {
        if (!is_array($this->value)) {
            return $this->value;
        }

        if (empty($this->value) && ($this->meta['emptyObject'] ?? false)) {
            return new \stdClass();
        }

        $nodes = $this->value();

        if (\array_key_exists('$ref', $nodes) && !$merge) {
            return $nodes['$ref']->doDenormalize($merge, [...$visited, $this]);
        }

        $denormalized = [];

        foreach ($nodes as $path => $node) {
            if ($node instanceof ReferenceNode) {
                $denormalizedRef = $node->doDenormalize($merge, [...$visited, $this]);

                $denormalized = array_merge(
                    $denormalized,
                    // TODO: examine boolean schemas
                    is_scalar($denormalizedRef)
                        ? [$denormalized]
                        : $denormalizedRef
                );
            } else {
                $denormalized[$path] = $node->doDenormalize($merge, [...$visited, $this]);
            }
        }

        return $denormalized;
    }

    public function getIterator(): \Iterator
    {
        return $this->walk();
    }

    public function walk(): \Generator
    {
        return $this->doWalk($this);
    }

    private function doWalk(Node $node): \Generator
    {
        yield $node;

        if (!$node instanceof ReferenceNode && is_array($value = $node->value())) {
            foreach ($value as $child) {
                yield from $this->doWalk($child);
            }
        }
    }

    private function nodesToRoot(): \Generator
    {
        $node = $this;

        while (!is_null($node = $node->parent())) {
            yield $node;
        }
    }

    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \RuntimeException('Node is read only');
    }

    public function offsetUnset($offset): void
    {
        throw new \RuntimeException('Node is read only');
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return $this->denormalize();
    }
}
