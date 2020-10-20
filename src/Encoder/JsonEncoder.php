<?php declare(strict_types=1);

namespace OAS\Resolver\Encoder;

use OAS\Resolver\EncoderInterface;

class JsonEncoder implements EncoderInterface
{
    private $depth;
    private $options;

    /**
     * @param int $options is passed to json_encode as second param
     * @param int $depth   is passed to json_encode as third param
     */
    public function __construct(int $options = 0, int $depth = 512)
    {
        $this->options = $options;
        $this->depth = $depth;
    }

    public function encode(array $graph)
    {
        return json_encode($graph, $this->options, $this->depth);
    }
}
