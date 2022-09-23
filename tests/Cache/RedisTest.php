<?php

namespace Solar\MicroFramework\Tests\Cache;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\Adapter\Redis;

class RedisTest extends CacheClientTest
{
    protected function checkEnvironment(): void
    {
        if (!class_exists('\\Redis')) {
            $this->markTestSkipped('Extension for Redis not installed');
        }
    }

    protected function getAdapter(): CacheInterface
    {
        return new Redis([
            'host'  => '127.0.0.1',
            'port'  => 63975
        ]);
    }
}