<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use OAS\Resolver\Factory\UriFactory;
use function OAS\Resolver\realPath;

class RealPathTest extends TestCase
{
    /**
     * @test
     * @dataProvider realPathTestDataProvider
     */
    public function itResolvesRealPath(UriInterface $uri, ?UriInterface $context, string $expectedResult)
    {
        $this->assertEquals($expectedResult, (string) realPath($uri, $context));
    }

    /**
     * @return array
     */
    public function realPathTestDataProvider(): array
    {
        $uriFactory = new UriFactory();

        return [
            [
                $uriFactory->createUri('/highway/from/../to/hell'),
                $uriFactory->createUri('/'),
                '/highway/to/hell'
            ],
            // no context path provided: '/' taken as default
            [
                $uriFactory->createUri('/highway/from/../to/hell'),
                null,
                '/highway/to/hell'
            ],
            [
                $uriFactory->createUri('/hell'),
                $uriFactory->createUri('/highway/to'),
                '/highway/to/hell'
            ],
            [
                $uriFactory->createUri('hell'),
                $uriFactory->createUri('/highway/to'),
                '/highway/to/hell'
            ],
            [
                $uriFactory->createUri('hell/../heaven'),
                $uriFactory->createUri('/highway/to'),
                '/highway/to/heaven'
            ]
        ];
    }
}
