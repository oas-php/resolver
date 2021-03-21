<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OAS\Resolver\Graph\Node;
use OAS\Resolver\Factory\UriFactory;
use OAS\Resolver\Factory\TreeFactory;

class NodeTest extends TestCase
{
    private const URI = 'http://example.com/schema.json';

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::buildGraph
     * @dataProvider graphProvider
     */
    public function itIsInstanceOfNode(Node $node): void
    {
        $this->assertInstanceOf(Node::class, $node);
    }

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::find
     * @covers       \OAS\Resolver\Graph\Node::value
     * @dataProvider graphProvider
     */
    public function itFindsNodeInGraphWithGivenPath(Node $node): void
    {
        $this->assertEquals(
            'string', $node->find('/properties/a/type')->value()
        );

        $this->assertEquals(
            'string', $node->find('/properties/a')->find('./type')->value()
        );

        $this->assertSame(
            $node, $node->find('.')
        );

        $this->assertSame(
            $node, $node->find('/properties')->find('..')
        );
    }

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::path
     * @covers       \OAS\Resolver\Graph\Node::pathFromRoot
     * @covers       \OAS\Resolver\Graph\Node::nodesToRoot
     * @dataProvider graphProvider
     */
    public function itGetsPathFromRoot(Node $node): void
    {
        $this->assertEquals(
            '/', $node->path()
        );

        $this->assertEquals(
            '/properties', $node['properties']->path()
        );

        $this->assertEquals(
            '/properties/a/type', $node['properties']['a']['type']->path()
        );
    }

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::pathFromParent
     * @dataProvider graphProvider
     */
    public function itGetsPathFromParent(Node $node): void
    {
        $this->assertEquals(
            '', $node->pathFromParent()
        );

        $this->assertEquals(
            'properties', $node['properties']->pathFromParent()
        );

        $this->assertEquals(
            'type', $node['properties']['a']['type']->pathFromParent()
        );
    }

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::offsetExists
     * @covers       \OAS\Resolver\Graph\Node::offsetGet
     * @dataProvider graphProvider
     */
    public function itImplementsArrayAccessInterface(Node $node): void
    {
        $this->assertInstanceOf(
            Node::class, $node['properties']['b']['type']
        );

        $this->assertEquals(
            'number', $node['properties']['b']['type']->value()
        );
    }

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::getIterator
     * @covers       \OAS\Resolver\Graph\Node::walk
     * @covers       \OAS\Resolver\Graph\Node::doWalk
     * @dataProvider graphProvider
     */
    public function itImplementsIteratorInterface(Node $graph): void
    {
        foreach ($graph as $node) {
            $this->assertInstanceOf(Node::class, $node);
        }

        $walker = $graph->walk();
        $this->assertSame($graph, $walker->current());

        $walker->next();
        $this->assertSame($graph->find('/properties'), $walker->current());

        $walker->next();
        $this->assertSame($graph->find('/properties/a'), $walker->current());

        $walker->next();
        $this->assertSame($graph->find('/properties/a/type'), $walker->current());

        $walker->next();
        $this->assertSame($graph->find('/properties/b'), $walker->current());

        $walker->next();
        $this->assertSame($graph->find('/properties/b/type'), $walker->current());

        $walker->next();
        $this->assertFalse($walker->valid());
    }

    /**
     * @test
     * @covers       \OAS\Resolver\Graph\Node::uri
     * @covers       \OAS\Resolver\Graph\Node::buildGraph
     * @dataProvider graphProvider
     */
    public function itGetsUri(Node $node): void
    {
        $this->assertEquals(
            self::URI, $node->uri()
        );

        $this->assertEquals(
            self::URI.'#/properties', $node['properties']->uri()
        );
    }

    /**
     * @test
     * @covers \OAS\Resolver\Graph\Node::denormalize
     */
    public function itConvertsGraphBackToArray(): void
    {
        $uriFactory = new UriFactory();
        $treeFactory = new TreeFactory($uriFactory);

        $rawGraph = $this->getRawGraph();
        $graph = $treeFactory->create(
            $rawGraph,
            $uriFactory->createUri(self::URI)
        );

        $this->assertEquals(
            $rawGraph, $graph->denormalize()
        );
    }

    public function graphProvider(): array
    {
        return [
            [
                $this->getGraph()
            ]
        ];
    }

    private function getGraph(): Node
    {
        $uriFactory = new UriFactory();
        $treeFactory = new TreeFactory($uriFactory);

        return $treeFactory->create(
            $this->getRawGraph(),
            $uriFactory->createUri(self::URI)
        );
    }

    private function getRawGraph(): array
    {
        return [
            'properties' => [
                'a' => [
                    'type' => 'string'
                ],
                'b' => [
                    'type' => 'number'
                ]
            ]
        ];
    }
}
