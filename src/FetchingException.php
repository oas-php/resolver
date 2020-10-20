<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriInterface;

class FetchingException extends \RuntimeException
{
    private $uri;

    public function __construct(UriInterface $uri, $message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct("Resource is not reachable under provided URI: " . (string) $uri, $code, $previous);
        $this->uri = $uri;
    }
}
