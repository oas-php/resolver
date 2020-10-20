<?php declare(strict_types=1);

namespace OAS\Resolver\Encoder;

use OAS\Resolver\EncoderInterface;

class ArrayEncoder implements EncoderInterface
{
    public function encode(array $graph)
    {
        return $graph;
    }
}
