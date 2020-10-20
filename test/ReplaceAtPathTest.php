<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use function OAS\Resolver\replaceAtPath;

class ReplaceAtPathTest extends TestCase
{
    /**
     * @test
     * @dataProvider replaceAtPathTestDataProvider
     */
    public function itReplacesGraphNodeAtGivenPath(array $graph, array $path, $replacement, $expectedGraph): void
    {
        $this->assertEquals($expectedGraph, replaceAtPath($graph, $path, $replacement));
    }

    /**
     * @test
     */
    public function itRaisesErrorWhenPathDoesNotExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Path "shortest -> way -> to" does not exist');
        replaceAtPath(
            [
                'high' => [
                    'way' => [
                        'to' => 'hell'
                    ]
                ]
            ],
            ['shortest', 'way', 'to'],
            'heaven'
        );
    }

    /**
     * @return array
     */
    public function replaceAtPathTestDataProvider(): array
    {
        return [
            [
                [
                    'high' => [
                        'way' => [
                            'to' => 'hell'
                        ]
                    ]
                ],
                ['high', 'way', 'to'],
                'heaven',
                [
                    'high' => [
                        'way' => [
                            'to' => 'heaven'
                        ]
                    ]
                ],
            ],
            [
                [
                    'high' => [
                        'way' => [
                            'to' => 'hell'
                        ]
                    ]
                ],
                [],
                'heaven',
                'heaven'
            ]
        ];
    }
}
