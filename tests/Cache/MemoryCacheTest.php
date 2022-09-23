<?php

namespace Solar\MicroFramework\Tests\Cache;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\Adapter\MemoryCache;

class MemoryCacheTest extends CacheClientTest
{

    protected function checkEnvironment(): void
    {
        // No specific extension dependency
    }

    protected function getAdapter(): CacheInterface
    {
        return new MemoryCache();
    }
}