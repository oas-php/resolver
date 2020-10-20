<?php declare(strict_types=1);

namespace OAS\Resolver;

class DecodingException extends \RuntimeException
{
    private $schema;

    public function __construct(string $schema, $message = "", $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->schema = $schema;
    }

    public function getSchema(): string
    {
        return $this->schema;
    }
}
