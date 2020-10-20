<?php declare(strict_types=1);

namespace OAS\Resolver\Encoder;

use Symfony\Component\Yaml\Yaml;
use OAS\Resolver\EncoderInterface;

class YamlEncoder implements EncoderInterface
{
    private $inline;
    private $indent;
    private $options;

    /**
     * @param int $inline   is passed to Symfony\Component\Yaml\Yaml::dump as second param
     * @param int $indent   is passed to Symfony\Component\Yaml\Yaml::dump as third param
     * @param int $options  is passed to Symfony\Component\Yaml\Yaml::dump as fourth param
     */
    public function __construct(int $inline = 32, int $indent = 2, int $options = Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE)
    {
        $this->inline = $inline;
        $this->indent = $indent;
        $this->options = $options;
    }

    public function encode(array $graph)
    {
        return Yaml::dump($graph, $this->inline, $this->indent, $this->options);
    }
}
