<?php

namespace Solar\MicroFramework\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\AbstractAdapter;
use Memcache as MemcacheResource;
use DateInterval;

class Memcache extends AbstractAdapter implements CacheInterface
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @var MemcacheResource
     */
    protected MemcacheResource $cache;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->cache = new MemcacheResource();
        $this->config = $config;

        $this->connect();
    }

    /**
     * @return void
     */
    public function connect(): void
    {
        $servers = (array) $this->config['servers'];

        foreach ($servers as $server) {
            $this->cache->addServer($server['host'], $server['port']);
        }
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        static::testKey($key);

        return $this->cache->get($key);
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        static::testKey($key);
        $expire = static::convertTtlToSeconds($ttl);

        return $this->cache->set($key, $value, null, $expire);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        static::testKey($key);

        return $this->cache->delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->cache->flush();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = static::convertKeysToArray($keys);
        $values = [];

        foreach ($keys as $key) {
            $value = $this->cache->get($key);
            $values[$key] = $value !== false ? $value : $default;
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        $values = static::convertValuesToArray($values);
        $expire = static::convertTtlToSeconds($ttl);
        $count = 0;

        foreach ($values as $key => $value) {
            $count += (int) $this->cache->set($key, $value, null, $expire);
        }

        return $count > 0;
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = static::convertKeysToArray($keys);
        $count = 0;

        foreach ($keys as $key) {
            $count += (int) $this->cache->delete($key);
        }

        return $count > 0;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== false;
    }
}