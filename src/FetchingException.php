<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

class FetchingException extends \RuntimeException
{
    private UriInterface $uri;
    private string $reason;

    public function __construct(UriInterface $uri, string $reason, $code = 0, \Throwable $previous = null)
    {
        $this->uri = $uri;
        $this->reason = $reason;

        parent::__construct(
            sprintf(
                'Resource is not reachable under provided URI: %s (reason: %s)',
                $uri,
                $reason
            ),
            $code,
            $previous
        );
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function uri(): UriInterface
    {
        return $this->uri;
    }
}
