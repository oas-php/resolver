<?php declare(strict_types=1);

namespace OAS\Resolver\Decoder;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use OAS\Resolver\DecoderInterface;
use OAS\Resolver\DecodingException;

class YamlDecoder implements DecoderInterface
{
    private $options;

    /**
     * @param int $options is passed to Symfony\Component\Yaml\Yaml::parse as second param
     */
    public function __construct(int $options = 0)
    {
        $this->options = $options;
    }

    public function decode(string $encoded, string $format = null): array
    {
        try {
            return Yaml::parse($encoded, $this->options);
        } catch (ParseException $exception) {
            throw new DecodingException($encoded, $exception->getMessage(), 0, $exception);
        }
    }
}
