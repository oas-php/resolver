<?php declare(strict_types=1);

namespace OAS\Resolver;

class ReferenceWalker
{
    private $generator;

    public function __construct($graph)
    {
        if (!is_array($graph) && !$graph instanceof Reference) {
            throw new \InvalidArgumentException(
                'The first parameter must be of array|\OAS\Resolver\Reference type'
            );
        }

        $this->generator = $this->doWalk($graph);
    }

    public function walk(): bool
    {
        return $this->generator->valid();
    }

    public function currentReference(): Reference
    {
        [$reference, $_] = $this->generator->current();

        return $reference;
    }

    public function nextReference(bool $goDeeper): void
    {
        $this->generator->send($goDeeper);
    }

    public function currentPath(): array
    {
        return $this->generator->key();
    }

    public function isRecursive(): bool
    {
        [$_, $isRecursive] = $this->generator->current();

        return $isRecursive;
    }

    private function doWalk($graph, array $path = [], array $visitedReferences = []): \Generator
    {
        yield from ($graph instanceof Reference)
            ? $this->yieldReference($graph, $path, $visitedReferences)
            : $this->walkGraph($graph, $path, $visitedReferences);
    }

    private function yieldReference(Reference $reference, array $path = [], array $visitedReferences = []): \Generator
    {
        $deeper = (bool) yield $path => [$reference, $this->alreadyVisited($visitedReferences, $reference)];

        if ($deeper) {
            yield from $this->doWalk(
                $reference->getResolved(), $path, append($visitedReferences, $reference)
            );
        }
    }

    private function walkGraph(array $graph, array $path = [], array $visitedReferences = []): \Generator
    {
        foreach ($graph as $pathSegment => $node) {
            if ($node instanceof Reference) {
                yield from $this->yieldReference($node, append($path, $pathSegment), $visitedReferences);
                continue;
            }

            if (is_array($node)) {
                yield from $this->doWalk($node, append($path, $pathSegment), $visitedReferences);
            }
        }
    }

    private function alreadyVisited(array $visitedReferences, Reference $node): bool
    {
        foreach ($visitedReferences as $visitedReference) {
            if ($node === $visitedReference) {
                return true;
            }
        }

        return false;
    }
}
