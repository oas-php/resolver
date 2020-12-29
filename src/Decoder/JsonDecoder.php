<?php declare(strict_types=1);

namespace OAS\Resolver\Decoder;

use OAS\Resolver\DecoderInterface;
use OAS\Resolver\DecodingException;

class JsonDecoder implements DecoderInterface
{
    private int $depth;
    private int $options;

    /**
     * @param int $depth    is passed to json_decode as third param
     * @param int $options  is passed to json_decode as fourth param
     */
    public function __construct(int $depth = 512, int $options = 0)
    {
        $this->depth = $depth;
        $this->options = $options;
    }

    public function decode(string $encoded)
    {
        try {
            return json_decode($encoded, true, $this->depth, $this->options | JSON_THROW_ON_ERROR);
        } catch (\JsonException $jsonException) {
            throw new DecodingException($encoded, $jsonException->getMessage(), 0, $jsonException);
        }
    }
}
