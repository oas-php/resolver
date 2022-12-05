<?php declare(strict_types=1);

namespace OAS\Resolver\Graph;

use Psr\Http\Message\UriInterface;
use function iter\any;
use function OAS\Resolver\{decode, equals, pathSegments};

class ReferenceNode extends Node
{
    protected string $ref;
    protected ?Node $resolved = null;

    public function __construct(string $ref)
    {
        $this->ref = $ref;
    }

    public function ref(): string
    {
        return $this->ref;
    }

    public function uri(): UriInterface
    {
        return $this->resolved()->uri();
    }

    public function canonicalUri(): UriInterface
    {
        return $this->resolved()->canonicalUri();
    }

    public function value()
    {
        return $this->resolved()->value();
    }

    public function resolve(Node $referenced, bool $parent = true): void
    {
        $this->resolved = $referenced;
        $referenced->references[] = $this;

        if ($parent) {
            $referenced->parent = $this;
        }
    }

    public function resolved(): Node
    {
        $resolved = $this->resolved;

        while ($resolved instanceof ReferenceNode) {
            $resolved = $resolved->resolved;
        }

        if (is_null($resolved)) {
            throw new \LogicException(
                'Reference is no resolved yet'
            );
        }

        return $resolved;
    }

    public function denormalize(bool $merge = false)
    {
        return $this->doDenormalize($merge, []);
    }

    protected function doDenormalize(bool $merge, array $visited)
    {
        if ($this->recursive($visited)) {
            $resolved = $this->resolved();
            $refs = [
                $resolved,
                ...array_filter(
                    $resolved->references,
                    fn(ReferenceNode $node) => $node !== $this
                )
            ];

            return ['$ref' => "#{$this->shortestRef($refs)}"];
        }

        return $this->resolved()->doDenormalize($merge, [...$visited, $this]);
    }

    private function recursive(array $visited): bool
    {
        return any(fn(Node $resolvedNode) => equals($resolvedNode->uri(), $this->uri()), $visited);
    }

    private function shortestRef(array $nodes): string
    {
        $sanitizedPathSegments = array_map(
            fn ($ref) => array_filter(
                pathSegments($ref),
                function (string $segment) {
                    return '$ref' !== decode($segment);
                }
            ),
            array_map(fn (Node $node) => $node->path(), $nodes)
        );

        $shortest = array_reduce(
            array_slice($sanitizedPathSegments, 1),
            fn (array $shortest, array $current) => count($shortest) > count($current)
                ? $current : $shortest,
            $sanitizedPathSegments[0]
        );

        return '/' . join('/', $shortest);
    }
}
