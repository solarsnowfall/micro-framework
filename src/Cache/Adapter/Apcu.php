<?php

namespace Solar\MicroFramework\Cache\Adapter;

use Solar\MicroFramework\Cache\AbstractAdapter;
use DateInterval;

class Apcu extends AbstractAdapter implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @inheritDoc
     */
    public function get(string $key, mixed $default = null): mixed
    {
        static::testKey($key);
        $value = apcu_fetch($key, $success);

        return $success ? $value : $default;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        static::testKey($key);
        $ttl = static::convertTtlToSeconds($ttl);

        return apcu_store($key, $value, $ttl);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        static::testKey($key);

        return apcu_delete($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = static::convertKeysToArray($keys);
        $values = apcu_fetch($keys, $success);

        return $success ? $values : [];
    }

    /**
     * @inheritDoc
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $values = static::convertValuesToArray($values);
        $ttl = static::convertTtlToSeconds($ttl);

        $errors = apcu_store($values, null, $ttl);

        return empty($errors);
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $success = apcu_delete($keys);

        return $success !== false;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return apcu_exists($key);
    }
}