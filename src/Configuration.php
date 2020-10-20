<?php declare(strict_types=1);

namespace OAS\Resolver;

use Psr\Http\Message\UriFactoryInterface;
use Psr\SimpleCache\CacheInterface;
use OAS\Resolver\Decoder\ChainableDecoder;

final class Configuration
{
    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var ChainableDecoder
     */
    private $decoder;

    /**
     * @var CacheInterface|null
     */
    private $cache;

    public function __construct(
        UriFactoryInterface $uriFactory = null,
        DecoderInterface $decoder = null,
        CacheInterface $cache = null
    ) {
        $this->uriFactory = $uriFactory ?? new Factory\UriFactory();
        $this->decoder = $decoder ?? Factory\DecoderFactory::create();

        try {
            $this->cache = $cache ?? Factory\CacheFactory::create();
        } catch (\RuntimeException $exception) {
        }
    }

    public function getUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory;
    }

    public function getCache(): ?CacheInterface
    {
        return $this->cache;
    }

    public function getDecoder(): DecoderInterface
    {
        return $this->decoder;
    }
}
