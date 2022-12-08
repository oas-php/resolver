<?php declare(strict_types=1);

require_once __DIR__ . '/ResolverSpy.php';

use PHPUnit\Framework\TestCase;

class FetchOptimizationTest extends TestCase
{
    /**
     * @test
     */
    public function itFetchesGivenResourceOnlyOnce(): void
    {
        $resolver = new ResolverSpy();
        $resolver->resolve(__DIR__ . '/schemas/user.json');

        foreach ($resolver->getFetchedResources() as $resourceUri => $fetchCount)  {
            $this->assertEquals(1, $fetchCount, "Resource {$resourceUri} was fetched {$fetchCount} times instead of once.");
        }

        $this->assertEquals(2, $resolver->getFetchedResourcesCount());
    }
}