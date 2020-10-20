<?php declare(strict_types=1);

namespace OAS\Resolver\Factory;

use OAS\Resolver\Encoder;

final class EncoderFactory
{
    public static function create(): Encoder
    {
        $encoder = new Encoder();
        $encoder->addFormatEncoder('array', new Encoder\ArrayEncoder());

        if (\function_exists('\json_encode')) {
            $encoder->addFormatEncoder('json', new Encoder\JsonEncoder());
        }

        if (\class_exists('\Symfony\Component\Yaml\Yaml')) {
            $encoder->addFormatEncoder('yaml', new Encoder\YamlEncoder());
        }

        return $encoder;
    }
}
