<?php

namespace Solar\MicroFramework\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Solar\Microframework\Trait\CloneWithTrait;

trait MessageTrait
{
    use CloneWithTrait;

    /**
     * @var StreamInterface
     */
    protected StreamInterface $body;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var array
     */
    protected array $headerNames = [];

    /**
     * @var string
     */
    protected string $protocol = '1.1';

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @param string $version
     * @return MessageTrait
     */
    public function withProtocolVersion($version): self
    {
        return $this->cloneWith(['protocol' => $version]);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];

        foreach ($this->headerNames as $name => $alias) {
            $headers[$name] = $this->headers[$alias];
        }

        return $headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return array_key_exists($name, $this->headerNames);
    }

    /**
     * @param string $name
     * @return array
     */
    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        return $this->headers[$this->headerNames[$name]];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        $values = $this->getHeader($name);

        return implode(',', $values);
    }

    /**
     * @param string $name
     * @param array|string $value
     * @return Request|MessageTrait
     */
    public function withHeader($name, $value): self
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Argument 1 must be of type string');
        }

        if (!is_string($value) && !is_array($value)) {
            throw new InvalidArgumentException('Argument 2 must be of type string or array');
        }

        $headerNames = $this->headerNames;
        $headerNames[$name] = strtolower($name);

        $headers = $this->headers;
        $headers[$headerNames[$name]] = $value;

        return $this->cloneWith([
            'headers'       => $headers,
            'headerNames'   => $headerNames
        ]);
    }

    /**
     * @param string $name
     * @param array|string $value
     * @return Request|MessageTrait
     */
    public function withAddedHeader($name, $value): self
    {
        if (!is_string($name)) {
            throw new InvalidArgumentException('Argument 1 must be of type string');
        }

        if (!is_string($value) && !is_array($value)) {
            throw new InvalidArgumentException('Argument 2 must be of type string or array');
        }

        $headerNames = $this->headerNames;
        $headers = $this->headers;

        if (!$this->hasHeader($name)) {
            $headerNames[$name] = strtolower($name);
            $headers[$headerNames[$name]] = $value;
        } else {
            if (is_string($value)) {
                $value = (array) $value;
            }
            $headers[$headerNames[$name]] = array_merge($headers[$headerNames[$name]], $value);
        }

        return $this->cloneWith([
            'headers'       => $headers,
            'headerNames'   => $headerNames
        ]);
    }

    /**
     * @param string $name
     * @return Request|MessageTrait
     */
    public function withoutHeader($name): self
    {
        $headerNames = $this->headerNames;
        $headers = $this->headers;

        unset($headers[$headerNames[$name]], $headerNames[$name]);

        return $this->cloneWith(['headers' => $headers, 'headerNames' => $headerNames]);
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    /**
     * @param StreamInterface $body
     * @return Request|MessageTrait
     */
    public function withBody(StreamInterface $body): self
    {
        return $this->cloneWith(['body' => $body]);
    }

    /**
     * @param array|string|int $value
     * @return void
     */
    protected function formatHeaderValue(array|string|int &$value): void
    {
        $value = (array) $value;

        foreach (array_keys($value) as $key) {
            $value[$key] = trim((string) $value[$key], " \t");
        }
    }

    /**
     * @param string $header
     * @param array|string|int $values
     * @return void
     */
    protected function validateHeader(string $header, array|string|int $values): void
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

    /**
     * @param $body
     * @return $this
     */
    protected function setBody($body): self
    {
        if (!$body instanceof StreamInterface) {
            $body = new Stream($body);
        }

        $this->body = $body;

        return $this;
    }

    /**
     * @param array $headers
     * @return Request|MessageTrait
     */
    protected function setHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            $this->validateHeader($name, $value);
            $this->headerNames[$name] = mb_strtolower($name);

            if (is_string($value)) {
                $value = explode(', ', $value);
            }

            $this->headers[$this->headerNames[$name]] = $value;
        }

        return $this;
    }

    /**
     * @param string $method
     * @return void
     */
    protected function validateMethod(string $method): void
    {
        if (!in_array($method, static::VALID_METHODS)) {
            throw new InvalidArgumentException("Invalid http method: $method");
        }
    }
}