<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OAS\Resolver\Resolver;
use OAS\Resolver\Reference;
use OAS\Resolver\Configuration;
use OAS\Resolver\Decoder\YamlDecoder;
use OAS\Resolver\ReferenceWalker;
use function OAS\Resolver\retrieveByPath;

class ResolverTest extends TestCase
{
    /**
     * @test
     * @dataProvider paramsProvider
     */
    public function itResolvesAllRefs(Resolver $resolver, string $uri)
    {
        $resolved = $resolver->resolve($uri);
        $this->assertRefsResolved($resolved);
    }

    /**
     * @test
     */
    public function itRaisesErrorIsRefIsNotString()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('$ref must be of string type');

        $uri = stream_get_meta_data(tmpfile())['uri'];

        file_put_contents(
            $uri, json_encode(['$ref' => null])
        );

        (new Resolver())->resolve($uri);
    }

    /**
     * @test
     * @dataProvider paramsProvider
     */
    public function itDetectsRecursiveReferences(Resolver $resolver, string $uri)
    {
        $resolved = $resolver->resolve($uri);

        // Check the following recursion:
        //  Movie -(through: director)-> Director -(through: directed)-> Movie
        $movieA = retrieveByPath($resolved, ['components', 'schemas', 'Movie']);
        $director = retrieveByPath($movieA, ['properties', 'director']);
        $movieB = retrieveByPath($director, ['allOf', 1, 'properties', 'directed', 'items']);

        $this->assertSame($movieA, $movieB);
    }

    public function paramsProvider(): array
    {
        $yamlResolver = new Resolver(
            new Configuration(null, new YamlDecoder())
        );

        return [
            [
                $yamlResolver, 'http://localhost/library/openapi.yaml'
            ]
        ];
    }

    private function assertRefsResolved(array $graph): void
    {
        $referenceWalker = new ReferenceWalker($graph);

        while ($referenceWalker->walk()) {
            $this->assertInstanceOf(
                Reference::class,
                $referenceWalker->currentReference()
            );

            $referenceWalker->nextReference(
                !$referenceWalker->isRecursive()
            );
        }
    }
}

