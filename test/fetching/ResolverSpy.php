<?php declare(strict_types=1);

use Psr\Http\Message\UriInterface;

class ResolverSpy extends OAS\Resolver\Resolver
{
    private array $fetchedResources = [];

    protected function fetch(UriInterface $uri): string
    {
        $fetched = parent::fetch($uri);
        $uriWithoutFragment = (string) $uri->withFragment('');

        if (array_key_exists($uriWithoutFragment, $this->fetchedResources)) {
            $this->fetchedResources[$uriWithoutFragment]++;
        } else {
            $this->fetchedResources[$uriWithoutFragment] = 1;
        }

        return $fetched;
    }

    /**
     * @return array<string, int>
     */
    public function getFetchedResources(): array
    {
        return $this->fetchedResources;
    }

    public function getFetchedResourcesCount(): int
    {
        return array_sum($this->fetchedResources);
    }
}