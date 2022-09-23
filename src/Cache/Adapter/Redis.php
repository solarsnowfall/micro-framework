<?php

namespace Solar\MicroFramework\Cache\Adapter;

use DateInterval;
use Psr\SimpleCache\CacheInterface;
use Redis as RedisResource;
use RedisException;
use Solar\MicroFramework\Cache\AbstractAdapter;


class Redis extends AbstractAdapter implements CacheInterface
{
    protected RedisResource $cache;

    protected array $config;

    public function __construct(array $config)
    {
        $this->cache = new RedisResource();

        $this->config = $config;

        $this->connect();
    }

    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return bool
     * @throws RedisException
     */
    public function connect(): bool
    {
        $host = $this->config['host'] ?? '127.0.0.1';
        $port = $this->config['port'] ?? 6379;

        return $this->cache->connect($host, $port);
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
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        static::testKey($key);
        $expire = static::convertTtlToSeconds($ttl);

        if (!$expire) {
            return $this->cache->set($key, $value);
        }

        return $this->cache->setEx($key, $expire, $value);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        static::testKey($key);

        return $this->cache->del($key);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        return $this->cache->flushDB();
    }

    /**
     * @inheritDoc
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        static::convertKeysToArray($keys);
        $values = $this->cache->mGet($keys);

        foreach ($values as $key => $value) {
            if ($value === false) {
                $values[$key] = $default;
            }
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        $values = static::convertValuesToArray($values);
        $ttl = static::convertTtlToSeconds($ttl);

        $transaction = $this->cache->multi();

        foreach ($values as $key => $value) {
            if (!$ttl) {
                $transaction->set($key, $value);
            } else {
                $transaction->setEx($key, $ttl, $value);
            }
        }

        return !empty($transaction->exec());
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(iterable $keys): bool
    {
        static::convertKeysToArray($keys);

        return $this->cache->del($keys);
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        static::testKey($key);

        return $this->cache->exists($key);
    }
}