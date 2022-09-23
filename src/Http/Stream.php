<?php

namespace Solar\MicroFramework\Http;

use InvalidArgumentException;
use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class Stream implements StreamInterface
{
    public const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';

    public const WRITABLE_MODES = '/a|w|r\+|rw|x|c/';

    /**
     * @var bool
     */
    protected bool $seekable = false;

    /**
     * @var int|null
     */
    protected ?int $size = null;

    /**
     * @var bool
     */
    protected bool $readable = false;

    /**
     * @var resource|null
     */
    protected $stream;

    /**
     * @var bool
     */
    protected bool $writable = false;

    /**
     * @param $body
     */
    public function __construct($body = null)
    {
        if (!is_string($body) && !is_resource($body) && $body !== null) {
            throw new InvalidArgumentException('Argument 1 must be string or resource');
        }

        if (is_string($body)) {
            $resource = fopen('php//temp', 'w+');
            fwrite($resource, $body);
            $body = $resource;
        }

        $this->stream = $body;
        $this->setup();
    }

    protected function setup()
    {
        $meta = $this->getMetadata();

        if ($this->stream !== null) {
            $this->size = fstat($this->stream)['size'] ?? 0;
            $this->seekable = $meta['seekable'] ?? false;
            $this->readable = preg_match(static::READABLE_MODES, $meta['mode']);
            $this->writable = preg_match(static::WRITABLE_MODES, $meta['mode']);
        }
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        try {

            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();

        } catch (Exception $exception) {

            return '';
        }
    }

    /**
     * @inheritDoc
     */
    public function close(): void
    {
        if (is_readable($this->stream)) {
            fclose($this->stream);
        }

        $this->detach();
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
        $resource = $this->stream;
        unset($this->stream);

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        if ($this->stream === null || false === $pos = ftell($this->stream)) {
            throw new RuntimeException('Unable to get current position');
        }

        return $pos;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return $this->stream !== null && feof($this->stream);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($whence !== SEEK_SET && $whence !== SEEK_CUR && $whence !== SEEK_END) {
            throw new InvalidArgumentException('Invalid value for argument 2');
        }

        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }

        if (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException("Unable to seek stream position $offset");
        }
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        if (!$this->seekable) {
            throw new RuntimeException("Steam is not seekable");
        }

        $this->seek(0);
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        if (!$this->writable) {
            throw new RuntimeException('Stream is not writeable');
        }

        $result = @fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
        if (!$this->readable) {
            throw new RuntimeException('Stream is not readable');
        }

        $contents = fread($this->stream, $length);

        if ($contents === false) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $contents;
    }

    /**
     * @inheritDoc
     */
    public function getContents()
    {
        if (!$this->readable || false === $contents = stream_get_contents($this->stream)) {
            throw new RuntimeException('Unable to get stream contents');
        }

       return $contents;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        if ($this->stream === null) {
            return null;
        }

        $meta = stream_get_meta_data($this->stream);

        return $key === null ? $meta : $meta[$key] ?? null;
    }
}