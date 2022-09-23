<?php

namespace Solar\MicroFramework\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use Solar\MicroFramework\Cache\AbstractAdapter;
use DateInterval;

class MemoryCache extends AbstractAdapter implements CacheInterface
{
    protected array $cache = [];

    protected array $expiration = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->retrieveValue($key, $default);
    }

    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        static::testKey($key);
        $ttl = static::convertTtlToSeconds($ttl);
        $this->setValue($key, $value, $ttl);

        return true;
    }

    public function delete(string $key): bool
    {
        static::testKey($key);
        unset($this->cache[$key], $this->expiration[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->cache = $this->expiration = [];

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = static::convertKeysToArray($keys);
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = array_key_exists($key, $this->cache)
                ? unserialize($this->cache[$key])
                : $default;
        }

        return $values;
    }

    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        $values = static::convertValuesToArray($values);
        $ttl = static::convertTtlToSeconds($ttl);

        foreach ($values as $key => $value) {
            $this->setValue($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $keys = static::convertKeysToArray($keys);

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has(string $key): bool
    {
        static::testKey($key);
        $this->purgeExpired();

        return array_key_exists($key, $this->cache);
    }

    protected function retrieveValue(string $key, mixed $default = null)
    {
        $this->purgeExpired();

        if (!array_key_exists($key, $this->cache)) {
            return $default;
        }

        return unserialize($this->cache[$key]);
    }

    protected function setValue(string $key, mixed $value, int $expiration = 0): static
    {
        $this->cache[$key] = serialize($value);
        $this->expiration[$key] = time() + $expiration;

        return $this;
    }

    protected function purgeExpired(): static
    {
        foreach (array_keys($this->cache) as $key) {
            if (time() > $this->expiration[$key]) {
                unset($this->cache[$key], $this->expiration[$key]);
            }
        }

        return $this;
    }
}