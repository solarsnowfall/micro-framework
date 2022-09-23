<?php

namespace Solar\MicroFramework\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    protected array $headers = [];

    protected string $protocol;

    public function __construct()
    {
        $this->protocol = $_SERVER['SERVER_PROTOCOL'];
    }

    /**
     * @inheritDoc
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version): self
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $clone = clone $this;
        $clone->protocol = $version;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
     */
    public function hasHeader($name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function getHeaderLine($name)
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value): self
    {
        $this->validateHeader($name, $value);
        $this->formatHeaderValue($value);

        $clone = clone $this;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        // TODO: Implement withAddedHeader() method.
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        // TODO: Implement withoutHeader() method.
    }

    /**
     * @inheritDoc
     */
    public function getBody()
    {
        // TODO: Implement getBody() method.
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        // TODO: Implement withBody() method.
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget()
    {
        // TODO: Implement getRequestTarget() method.
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        // TODO: Implement withRequestTarget() method.
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        // TODO: Implement getMethod() method.
    }

    /**
     * @inheritDoc
     */
    public function withMethod($method)
    {
        // TODO: Implement withMethod() method.
    }

    /**
     * @inheritDoc
     */
    public function getUri()
    {
        // TODO: Implement getUri() method.
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        // TODO: Implement withUri() method.
    }

    protected function formatHeaderValue(array|string|int &$value)
    {
        $value = (array) $value;

        foreach (array_keys($value) as $key) {
            $value[$key] = trim((string) $value[$key], " \t");
        }
    }

    protected function validateHeader(string $header, array|string|int $values)
    {
        if (preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $header) !== 1) {
            throw new InvalidArgumentException("Invalid header name provided: $header");
        }

        $values = (array) $values;

        foreach ($values as $value) {
            if (preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", $value) !== 1) {
                throw new InvalidArgumentException("Invalid header value provided: $value");
            }
        }
    }


}