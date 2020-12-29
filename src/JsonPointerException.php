<?php declare(strict_types=1);

namespace OAS\Resolver;

class JsonPointerException extends \RuntimeException
{
    public function __construct(string $jsonPointer, $code = 0, \Throwable $previous = null)
    {
        $message = "Evaluation of JSON Pointer failed: there is no path \"{$jsonPointer}\" in the graph";
        parent::__construct($message, $code, $previous);
    }
}
