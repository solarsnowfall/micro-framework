<?php

namespace Solar\MicroFramework\Cache;

use DateInterval;
use DateTime;

class AbstractAdapter
{
    const DEFAULT_TTL = null;

    /**
     * @param string $key
     * @return void
     */
    public static function testKey(string $key): void
    {
        if (preg_match('/^[0-9a-z-_]+$/i', $key) === false) {
            throw new InvalidCacheKeyException("Invalid key: $key");
        }
    }

    /**
     * @param int|DateInterval|null $ttl
     * @return int
     */
    protected static function convertTtlToSeconds(null|int|DateInterval $ttl): int
    {
        if ($ttl === null && null === $ttl = static::DEFAULT_TTL) {
            return 0;
        }

        if (is_int($ttl)) {
            return $ttl;
        }

        $dtNow = new DateTime();
        $dtExpire = clone $dtNow;
        $dtExpire->add($ttl);

        return $dtExpire->getTimestamp() - $dtNow->getTimestamp();
    }

    /**
     * @param iterable $keys
     * @return array
     */
    protected static function convertKeysToArray(iterable $keys): array
    {
        $keyList = [];

        foreach ($keys as $key) {
            static::testKey($key);
            $keyList[] = (string) $key;
        }

        return $keyList;
    }

    /**
     * @param iterable $values
     * @return array
     */
    protected static function convertValuesToArray(iterable $values): array
    {
        $valueList = [];

        foreach ($values as $key => $value) {
            static::testKey($key);
            $valueList[$key] = $value;
        }

        return $valueList;
    }
}