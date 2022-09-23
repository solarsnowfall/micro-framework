<?php

namespace Solar\MicroFramework\Tests\Cache;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\Adapter\Apcu;

class ApcuTest extends CacheClientTest
{
    protected function checkEnvironment(): void
    {
        if (!function_exists('apcu_store')){
            $this->markTestSkipped("Extension for apcu not installed");
        }
    }

    protected function getAdapter(): CacheInterface
    {
        return new Apcu();
    }
}