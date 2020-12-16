<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

class FetchingException extends \RuntimeException
{
    private $uri;

    public function __construct(UriInterface $uri, string $reason, $code = 0, \Throwable $previous = null)
    {
        $this->uri = $uri;
        parent::__construct(
            sprintf('Resource is not reachable under provided URI: %s. Error message: %s', (string) $uri, $reason),
            $code,
            $previous
        );
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }
}
