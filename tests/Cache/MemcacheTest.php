<?php

namespace Solar\MicroFramework\Tests\Cache;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\Adapter\Memcache;

class MemcacheTest extends CacheClientTest
{
    protected function checkEnvironment(): void
    {
        if (!class_exists('\\Memcache')) {
            $this->markTestSkipped('Extension for Memcache not installed');
        }
    }

    protected function getAdapter(): CacheInterface
    {
        return new Memcache([
            'servers' => [
                ['localhost', 11211]
            ]
        ]);
    }
}