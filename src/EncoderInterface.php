<?php declare(strict_types=1);

namespace OAS\Resolver;

interface EncoderInterface
{
    public function encode(array $graph);
}