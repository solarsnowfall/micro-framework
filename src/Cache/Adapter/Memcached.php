<?php

namespace Solar\MicroFramework\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\AbstractAdapter;
use Memcached as MemcachedResource;
use DateInterval;

class Memcached extends AbstractAdapter implements CacheInterface
{
    /**
     * @var array
     */
    protected array $config;

    /**
     * @var MemcachedResource
     */
    protected MemcachedResource $cache;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->cache = new MemcachedResource();

        $this->config = $config;

        $this->connect();
    }

    /**
     * @return void
     */
    public function connect()
    {
        $servers = $this->config['servers'] ?? [];
        $options = $this->config['options'] ?? [];

        if (!empty($servers)) {
            $this->cache->addServers($servers);
        }

        if (!empty($options)) {
            $this->cache->setOptions($options);
        }
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        static::testKey($key);

        if (
            false === $value = $this->cache->get($key)
            && $this->cache->getResultCode() === MemcachedResource::RES_NOTFOUND
        ) {
            return $default;
        }

        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param DateInterval|int|null $ttl
     * @return bool
     */
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        static::testKey($key);
        $expiration = static::convertTtlToSeconds($ttl);

        $result = $this->cache->set($key, $value, $expiration);

        if (
            $value === false
            && $result === false
            && $this->cache->getResultCode() === MemcachedResource::RES_SUCCESS
        ) {
            return true;
        }

        return $result;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        static::testKey($key);

        return $this->delete($key);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->cache->flush();
    }

    /**
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = static::convertKeysToArray($keys);
        $values = [];

        if (false !== $found = $this->cache->getMulti($keys)) {
            foreach ($keys as $key) {
                $values[$key] = array_key_exists($key, $found) ? $found[$key] : $default;
            }
        }

        return $values;
    }

    /**
     * @param iterable $values
     * @param DateInterval|int|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $expiration = static::convertTtlToSeconds($ttl);
        $values = static::convertValuesToArray($values);

        return $this->cache->setMulti($values, $expiration);
    }

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = static::convertKeysToArray($keys);

        return empty($keys) || $this->faultCheck($this->cache->deleteMulti($keys));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        static::testKey($key);

        return (
            false !== $this->cache->get($key)
            || $this->cache->getResultCode() === MemcachedResource::RES_SUCCESS
        );
    }

    /**
     * @param $result
     * @return bool
     */
    protected function faultCheck($result): bool
    {
        return (
            false !== $result
            || $this->cache->getResultCode() === MemcachedResource::RES_NOTFOUND
        );
    }
}