<?php declare(strict_types=1);

namespace OAS\Resolver;

interface DecoderInterface
{
    /**
     * @param string $encoded
     * @throws DecodingException
     * @return array|scalar
     */
    public function decode(string $encoded);
}
