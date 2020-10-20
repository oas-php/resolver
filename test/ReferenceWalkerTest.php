<?php declare(strict_types=1);

use OAS\Resolver\ReferenceWalker;
use PHPUnit\Framework\TestCase;
use OAS\Resolver\Reference;
use function OAS\Resolver\retrieveByPath;

class ReferenceWalkerTest extends TestCase
{
    /**
     * @test
     */
    public function itTraverseTheGraphInPreOrderFashion()
    {
        $refF = null;

        $graph = [
            'a' => 'whatever',
            'refA' => $refA = Reference::create('#a', [
                'refB' => $refB = Reference::create('#b', [
                    'refC' => $refC = Reference::create('#c', [
                        'cc' => 'whatever'
                    ])
                ])
            ]),
            'b' => [
                'bb' => $refD = Reference::create('#d', [
                    'bbb' => [
                        'bbbb' => 'whatever'
                    ]
                ])
            ],
            'refE' => $refE = Reference::createDeferred('#e', function(Reference $refE) use (&$refF) {
                $refF = Reference::create('#f', [
                    'refE' => $refE
                ]);

                return [
                    'refF' => $refF
                ];
            })
        ];

        $referenceWalker = new ReferenceWalker($graph);

        // the order the walker should yield
        // references from the graph
        $orderedReferences = [
            [$refA, false], [$refB, false], [$refC, false], [$refD, false], [$refE, false], [$refF, false], [$refE, true]
        ];

        while ($referenceWalker->walk()) {
            [$expectedReference, $recursionStarted] = array_shift($orderedReferences);
            $currentReference = $referenceWalker->currentReference();

            $this->assertSame(
                $expectedReference,
                $currentReference
            );

            $this->assertSame(
                retrieveByPath($graph, $referenceWalker->currentPath()),
                $currentReference
            );

            $this->assertEquals($recursionStarted, $referenceWalker->isRecursive());

            $referenceWalker->nextReference(
                !$referenceWalker->isRecursive()
            );
        }
    }
}
