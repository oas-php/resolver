<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OAS\Resolver\Graph\Node;
use OAS\Resolver\Graph\ReferenceNode;
use function OAS\Resolver\jsonPointerDecode;
use function OAS\Resolver\pathSegments;

const SAMPLE_DOCUMENT_URI = __DIR__ . '/../vendor/oas-php/sample/docs/library/openapi.json';

class ResolverTest extends TestCase
{
    /**
     * @test
     * @covers \OAS\Resolver\Resolver::resolve
     * @covers \OAS\Resolver\Resolver::resolveDecoded
     * @covers \OAS\Resolver\Graph\Node::find
     * @covers \OAS\Resolver\Graph\Node::denormalize
     * @dataProvider dataProvider
     */
    public function itResolvesRefsCorrectly(Node $resolved, array $assertions): void
    {
        [$referencesAssertions, $denormalizedAssertions] = $assertions;

        //$this->assertNotEmpty($referencesAssertions);
        //$this->assertNotEmpty($denormalizedAssertions);

        foreach ($referencesAssertions as $referencesAssertion) {
            [$referencePath, $value] = $referencesAssertion;
            $reference = $resolved->find($referencePath);

            $this->assertInstanceOf(ReferenceNode::class, $reference);

            $this->assertEquals($reference->value(), $value);
        }

        $denormalized = $resolved->denormalize();

        foreach ($denormalizedAssertions as $denormalizedAssertion) {
            [$path, $denormalizedPart] = $denormalizedAssertion;
            $this->assertEquals($denormalizedPart, retrieveByPath($denormalized, $path));
        }
    }

    public function dataProvider(): array
    {
        $resolver = new OAS\Resolver\Resolver;

        return [
            // test 1
            [
                // resolved
                $resolver->resolveDecoded(
                    [
                        'a' => [
                            '$ref' => '#/defs/a'
                        ],
                        'defs' => [
                            'a' => true
                        ]
                    ]
                ),
                // assertions
                [
                    // resolved
                    [
                        [
                            // path
                            '/a/$ref',
                            // value
                            true
                        ]
                    ],
                    // denormalized
                    [
                        [
                            // path
                            '',
                            // value
                            ['a' => true, 'defs' => ['a' => true]]
                        ]
                    ]
                ]
            ],
            // test 2
            [
                $resolved = $resolver->resolveDecoded(
                    [
                        'a' => [
                            '$ref' => '#/'
                        ]
                    ]
                ),
                [
                    [
                        [
                            '/a/$ref',
                            $resolved->value()
                        ]
                    ],
                    [
                        [
                            '',
                            ['a' => ['$ref' => '#/']]
                        ]
                    ]
                ]
            ],
            // test 3
            [
                $resolved = $resolver->resolveDecoded(
                    [
                        'a' => [
                            '$ref' => '#/b'
                        ],
                        'b' => [
                            '$ref' => '#/c'
                        ],
                        'c' => [
                            '$ref' => '#/b'
                        ]
                    ]
                ),
                // assertions
                [
                    // resolved
                    [
                        [
                            '/a/$ref',
                            $resolved['b']->value()
                        ],
                        [
                            '/b/$ref',
                            $resolved['c']->value()
                        ],
                        [
                            '/c/$ref',
                            $resolved['b']->value()
                        ],
                        [
                            '/b/$ref/$ref',
                            $resolved['b']->value()
                        ],
                        [
                            '/c/$ref/$ref',
                            $resolved['c']->value()
                        ]
                    ],
                    // denormalized
                    [
                        // TODO : if refs are recursive and not-resolvable
                        // consider not to change them at all during de-normalization
                        // process
                        [
                            '',
                            [
                                'a' => [
                                    '$ref' => '#/b'
                                ],
                                'b' => [
                                    '$ref' => '#/b'
                                ],
                                'c' => [
                                    '$ref' => '#/c'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // test 4
            [
                $resolved = $resolver->resolveDecoded(
                    [
                        'a' => [
                            'b' => [
                                '$ref' => '#/c'
                            ]
                        ],
                        'c' => [
                            '$ref' => '#/a'
                        ]
                    ]
                ),
                // assertions
                [
                    // resolved
                    [
                        [
                            '/a/b/$ref',
                            $resolved['c']->value()
                        ],
                        [
                            '/a/b/$ref/$ref',
                            $resolved['a']->value()
                        ],
                        [
                            '/c/$ref',
                            $resolved['a']->value()
                        ]
                    ],
                    // denormalized
                    [
                        [
                            '/c',
                            [
                                'b' => [
                                    '$ref' => '#/c'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // test 5
            [
                $resolver->resolve(SAMPLE_DOCUMENT_URI),
                // assertions
                [
                    // resolved
                    [],
                    // denormalized
                    [
                        [
                            '/paths/~1movies/get/responses/200/content/application~1json/schema/properties/data/items',
                            [
                                'type' => 'object',
                                'properties' => [
                                    'title' => [
                                        'type' => 'string'
                                    ],
                                    'genre' => [
                                        'type' => 'string'
                                    ],
                                    'year' => [
                                        'type' => 'integer'
                                    ],
                                    'director' => [
                                        'allOf' => [
                                            [
                                                'type' => 'object',
                                                'properties' => [
                                                    'firstName' => [
                                                        'type' => 'string'
                                                    ],
                                                    'lastName' => [
                                                        'type' => 'string'
                                                    ],
                                                    'yearOfBirth' => [
                                                        'type' => 'integer'
                                                    ]
                                                ],
                                                'required' => [
                                                    'firstName', 'lastName', 'yearOfBirth'
                                                ],
                                                'example' => [
                                                    'firstName' => 'Jack',
                                                    'lastName' => 'Nicholson',
                                                    'yearOfBirth' => 1937
                                                ]
                                            ],
                                            [
                                                'type' => 'object',
                                                'properties' => [
                                                    'directed' => [
                                                        'type' => 'array',
                                                        'items' => [
                                                            '$ref' => '#/components/schemas/Movie'
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'actors' => [
                                        'type' => 'array',
                                        'items' => [
                                            'allOf' => [
                                                [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'firstName' => [
                                                            'type' => 'string'
                                                        ],
                                                        'lastName' => [
                                                            'type' => 'string'
                                                        ],
                                                        'yearOfBirth' => [
                                                            'type' => 'integer'
                                                        ]
                                                    ],
                                                    'required' => [
                                                        'firstName', 'lastName', 'yearOfBirth'
                                                    ],
                                                    'example' => [
                                                        'firstName' => 'Jack',
                                                        'lastName' => 'Nicholson',
                                                        'yearOfBirth' => 1937
                                                    ]
                                                ],
                                                [
                                                    'type' => 'object',
                                                    'properties' => [
                                                        'actedId' => [
                                                            'type' => 'array',
                                                            'items' => [
                                                                '$ref' => '#/components/schemas/Movie'
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ],
                                'example' => [
                                    'title' => 'The Shining',
                                    'year' => 1980,
                                    'director' => [
                                        'firstName' => 'Stanley',
                                        'lastName' => 'Kubrick',
                                        'yearOfBirth' => 1928
                                    ],
                                    'actors' => [
                                        [
                                            'firstName' => 'Jack',
                                            'lastName' => 'Nicholson',
                                            'yearOfBirth' => 1937
                                        ],
                                        [
                                            'firstName' => 'Shelley',
                                            'lastName' => 'Duvall',
                                            'yearOfBirth' => 1949
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}

function retrieveByPath($graph, string $path)
{
    $current = &$graph;
    $path = array_map(
        fn (string $segment) => jsonPointerDecode($segment),
        pathSegments($path)
    );

    foreach ($path as $pathSegment) {
        if (!array_key_exists($pathSegment, $current)) {
            throw new \RuntimeException(sprintf('Path "%s" does not exist', \join(' -> ', $path)));
        }

        $current = &$current[$pathSegment];
    }

    return $current;
}
