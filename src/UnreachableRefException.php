<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

class UnreachableRefException extends \RuntimeException
{
    private UriInterface $src;
    private string $ref;

    public function __construct(UriInterface $src, string $ref, FetchingException $fetchingError, $code = 0)
    {
        $this->src = $src;
        $this->ref = $ref;
        parent::__construct(
            sprintf(
                'Resource is not reachable under provided URI: %s (source: %s, $ref: %s, reason: %s)',
                $fetchingError->uri(),
                $src,
                $ref,
                $fetchingError->reason()
            ),
            $code,
            $fetchingError
        );
    }

    public function ref(): string
    {
        return $this->ref;
    }

    public function src(): UriInterface
    {
        return $this->src;
    }

    public function uri(): UriInterface
    {
        /** @var FetchingException $fetchingException */
        $fetchingException = $this->getPrevious();

        return $fetchingException->uri();
    }

    public function reason(): string
    {
        /** @var FetchingException $fetchingException */
        $fetchingException = $this->getPrevious();

        return $fetchingException->reason();
    }
}
