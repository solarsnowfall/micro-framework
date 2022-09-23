<?php

namespace Solar\MicroFramework\Tests\Cache;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\Adapter\Memcached;

class MemcachedTest extends CacheClientTest
{
    protected function checkEnvironment(): void
    {
        if (!class_exists('Memcached')) {
            $this->markTestSkipped("Extension for Memcached not installed");
        }
    }

    protected function getAdapter(): CacheInterface
    {
        return new Memcached([
            'servers' => [
                ['localhost', 11211]
            ]
        ]);
    }
}