<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use OAS\Resolver\Factory\UriFactory;
use Psr\Http\Message\UriInterface;
use function OAS\Resolver\includes;

class IncludesTest extends TestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function itChecksIfUriIncludesInOtherUri(UriInterface $uriA, UriInterface $uriB, bool $includes): void
    {
        $this->assertEquals($includes, includes($uriA, $uriB));
    }

    public function dataProvider(): array
    {
        $uriFactory = new UriFactory();

        return [
            [
                $uriFactory->createUri('http://example.com/schemas/library.json'),
                $uriFactory->createUri('http://example.com/schemas/library.json'),
                true
            ],
            [
                $uriFactory->createUri('http://example.com/schemas/library.json'),
                $uriFactory->createUri('http://example.com/schemas/theater.json'),
                false
            ],
            [
                $uriFactory->createUri('http://example.com/schemas/library.json'),
                $uriFactory->createUri('http://www.example.com/schemas/library.json'),
                false
            ],
            [
                $uriFactory->createUri('http://example.com/schemas/library.json#/properties/friends/items'),
                $uriFactory->createUri('http://example.com/schemas/library.json#/'),
                false
            ],
            [
                $uriFactory->createUri('http://example.com/schemas/library.json#/'),
                $uriFactory->createUri('http://example.com/schemas/library.json#/properties/friends/items'),
                true
            ],
            [
                $uriFactory->createUri('http://example.com/schemas/library.json#/properties/friends/items'),
                $uriFactory->createUri('http://example.com/schemas/library.json'),
                false
            ],
            [
                $uriFactory->createUri('http://example.com/schemas/library.json'),
                $uriFactory->createUri('http://example.com/schemas/library.json#/properties/friends/items'),
                true
            ]
        ];
    }
}
