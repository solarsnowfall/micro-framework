<?php

namespace Solar\MicroFramework\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Solar\Microframework\Trait\CloneWithTrait;

trait MessageTrait
{
    use CloneWithTrait;

    protected StreamInterface $body;

    protected array $headers = [];

    protected array $headerNames = [];

    protected string $protocol = '1.1';

    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version): self
    {
        return $this->cloneWith(['protocol' => $version]);
    }

    public function getHeaders(): array
    {
        $headers = [];

        foreach ($this->headerNames as $name => $alias) {
            $headers[$name] = $this->headers[$alias];
        }

        return $headers;
    }

    public function hasHeader($name): bool
    {
        return array_key_exists($name, $this->headerNames);
    }

    public function getHeader($name): array
    {
        if (!$this->hasHeader($name)) {
            return [];
        }

        return $this->headers[$this->headerNames[$name]];
    }

    public function getHeaderLine($name): string
    {
        $values = $this->getHeader($name);

        return implode(',', $values);
    }

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

    public function withoutHeader($name): self
    {
        $headerNames = $this->headerNames;
        $headers = $this->headers;

        unset($headers[$headerNames[$name]], $headerNames[$name]);

        return $this->cloneWith(['headers' => $headers, 'headerNames' => $headerNames]);
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): self
    {
        return $this->cloneWith(['body' => $body]);
    }
}