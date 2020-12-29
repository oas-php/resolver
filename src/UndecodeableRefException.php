<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

class UndecodeableRefException extends \RuntimeException
{
    private UriInterface $src;
    private UriInterface $uri;
    private string $ref;

    public function __construct(
        UriInterface $src,
        UriInterface $uri,
        string $ref,
        DecodingException $decodingException
    ) {
        $this->src = $src;
        $this->uri = $uri;
        $this->ref = $ref;

        parent::__construct(
            sprintf(
                'Resource fetched from URI: %s could not be decoded using configured decoders (source: %s, $ref: %s)',
                $uri,
                $src,
                $ref,
            ),
            0,
            $decodingException
        );
    }

    public function src(): UriInterface
    {
        return $this->src;
    }

    public function uri(): UriInterface
    {
        return $this->uri;
    }

    public function ref(): string
    {
        return $this->ref;
    }

    public function resource(): string
    {
        /** @var DecodingException $decodingException */
        $decodingException = $this->getPrevious();

        return $decodingException->resource();
    }
}
