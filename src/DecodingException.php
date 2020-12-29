<?php declare(strict_types=1);

namespace OAS\Resolver;

class DecodingException extends \RuntimeException
{
    private string $resource;

    public function __construct(string $resource, $message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->resource = $resource;
    }

    public function resource(): string
    {
        return $this->resource;
    }
}
