<?php

namespace Solar\MicroFramework\Cache\Adapter;

use DateInterval;
use FilesystemIterator;
use Generator;
use InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Solar\MicroFramework\Cache\AbstractAdapter;

class FileCache extends AbstractAdapter implements CacheInterface
{
    const DEFAULT_TTL = 30*24*60*60;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $realPath = realpath($path);

        if ($realPath === false) {
            throw new InvalidArgumentException("Path not found: $path");
        }

        if (!is_writable($realPath)) {
            throw new InvalidArgumentException("Path can't be written to: $path");
        }

        $this->path = $realPath;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $values = $this->getMultiple([$key], $default);

        return $values[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param DateInterval|int|null $ttl
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function set(string $key, mixed $value, DateInterval|int|null $ttl = null): bool
    {
        return $this->setMultiple([$key => $value], $ttl);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete(string $key): bool
    {
        $filename = $this->getFilename($key);

        return @unlink($filename);
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        $files = $this->listFiles();

        foreach ($files as $filename) {
            @unlink($filename);
        }

        return true;
    }

    /**
     * @param iterable $keys
     * @param mixed|null $default
     * @return iterable
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $keys = (array) $keys;
        $values = [];

        foreach ($keys as $key) {

            $filename = $this->getFilename($key);

            if (!file_exists($filename)) {
                $values[$key] = $default;
                continue;
            }

            $modified = $this->getFileModificationTime($filename);

            if ($modified === false) {
                $values[$key] = $default;
                continue;
            }

            if ($modified < time()) {
                unlink($filename);
                $values[$key] = $default;
                continue;
            }

            $contents = file_get_contents($filename);
            $value = unserialize($contents);

            $values[$key] = $value ?? $default;
        }

        return $values;
    }

    /**
     * @param iterable $values
     * @param DateInterval|int|null $ttl
     * @return bool
     */
    public function setMultiple(iterable $values, DateInterval|int|null $ttl = null): bool
    {
        $values = static::convertValuesToArray($values);
        $seconds = static::convertTtlToSeconds($ttl);
        $mtime = $seconds !== 0 ? time() + $seconds : 0;
        $count = 0;

        foreach ($values as $key => $value) {

            $filename = $this->getFilename($key);
            $contents = serialize($value);

            $count += (int) $this->saveFile($filename, $contents, $mtime);
        }

        return count($values) === $count;
    }

    /**
     * @param iterable $keys
     * @return bool
     */
    public function deleteMultiple(iterable $keys): bool
    {
        $keys = static::convertKeysToArray($keys);
        $count = 0;

        foreach ($keys as $key) {
            $count += (int) $this->delete($key);
        }

        return count($keys) === $count;
    }

    /**
     * @param string $key
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== false;
    }

    /**
     * @return int
     */
    public function purgeExpired(): int
    {
        $files = $this->listFiles();
        $time = time();
        $count = 0;

        foreach ($files as $filename) {
            if (filemtime($filename) < $time) {
                @unlink($filename);
                $count++;
            }
        }

        return $count;
    }

    protected function getFilename(string $key, bool $testKey = true): string
    {
        if ($testKey) {
            static::testKey($key);
        }

        $hash = hash('sha256', $key);
        $path = str_split(substr($hash, 0, 2));
        $path[] = substr($hash, 2);

        return $this->path . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $path);
    }

    protected function getFileModificationTime(string $filename): int|false
    {
        return @filemtime($filename);
    }

    public function listFiles(): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $this->path,
                FilesystemIterator::SKIP_DOTS
            )
        );

        foreach ($iterator as $path) {
            yield $path;
        }
    }

    protected function saveFile(string $filename, string $contents, int $mtime): bool
    {
        if (!file_exists($filename)) {

            $parts = explode(DIRECTORY_SEPARATOR, $filename);
            $path = '';

            while (count($parts) > 1) {
                $path .= array_shift($parts);
                if (!file_exists($path)) {
                    mkdir($path, '0775');
                }
                $path .= DIRECTORY_SEPARATOR;
            }
        }

        if (@file_put_contents($filename, $contents) === false) {
            return false;
        }

        if ($mtime && !@touch($filename, $mtime)) {
            unlink($filename);
            return false;
        }

        return true;
    }
}