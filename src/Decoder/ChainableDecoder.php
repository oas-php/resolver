<?php declare(strict_types=1);

namespace OAS\Resolver\Decoder;

use OAS\Resolver\DecoderInterface;
use OAS\Resolver\DecodingException;

class ChainableDecoder implements DecoderInterface
{
    /**
     * @var DecoderInterface[]
     */
    private $decoders = [];

    /**
     * @param DecoderInterface[] $decoders
     */
    public function __construct(iterable $decoders = [])
    {
        foreach ($decoders as $decoder) {
            $this->addFormatDecoder($decoder);
        }
    }

    public function addFormatDecoder(DecoderInterface $decoder): void
    {
        $this->decoders[] = $decoder;
    }

    public function decode(string $encoded): array
    {
        foreach ($this->decoders as $decoder) {
            try {
                return $decoder->decode($encoded);
            } catch (DecodingException $exception) {
                continue;
           }
        }

        throw new DecodingException($encoded, 'Non of registered decoder was able to handle provided resource');
    }
}
