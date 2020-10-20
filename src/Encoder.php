<?php declare(strict_types=1);

namespace OAS\Resolver;

final class Encoder
{
    /**
     * map: format<string> => encoder<EncoderInterface>
     *
     * @var EncoderInterface[]
     */
    private $encoders = [];

    /**
     * @param EncoderInterface[] $formatEncoders map: format<string> => encoder<EncoderInterface>
     */
    public function __construct(iterable $formatEncoders = [])
    {
        foreach ($formatEncoders as $key => $encoder) {
            $this->addFormatEncoder($key, $encoder);
        }
    }

    public function addFormatEncoder(string $format, EncoderInterface $encoder): void
    {
        $this->encoders[$format] = $encoder;
    }

    /**
     * @param array $resolved
     * @param string $format
     * @param bool $includeEmbedded
     * @return mixed
     */
    public function encode(array $resolved, string $format, bool $includeEmbedded = false)
    {
        $refWalker = new ReferenceWalker($resolved);

        while ($refWalker->walk()) {
            $reference = $refWalker->currentReference();
            $path = $refWalker->currentPath();

            $resolve = !$refWalker->isRecursive()
                // if $ref is embedded resolve only if requested
                && (!$reference->isEmbedded() || $includeEmbedded);

            $resolved = replaceAtPath(
                $resolved,
                    $path,
                $resolve
                    ? $reference->getResolved()
                    : ['$ref' => $reference->getRef()]
            );

            $refWalker->nextReference($resolve);
        }

        return ($this->encoders[$format] ?? $this->unsupportedFormat($format))->encode($resolved);
    }

    private function unsupportedFormat(string $format): void
    {
        throw new \RuntimeException(
            \sprintf("Encoder for %s format not found. Known encoders: %s", $format, \join(', ', \array_keys($this->encoders)))
        );
    }
}
