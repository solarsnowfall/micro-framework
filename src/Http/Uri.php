<?php

namespace Solar\MicroFramework\Http;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    protected const SCHEME_PORTS = ['http' => 80, 'https' => 443];

    /**
     * @var string
     */
    protected string $fragment;

    /**
     * @var string
     */
    protected string $host;

    /**
     * @var string|null
     */
    protected ?string $password;

    /**
     * @var string
     */
    protected string $path;

    /**
     * @var int|null
     */
    protected ?int $port;

    /**
     * @var array
     */
    protected array $query = [];

    /**
     * @var string
     */
    protected string $scheme;

    /**
     * @var string|null
     */
    protected ?string $user;

    /**
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $parts = parse_url($uri);

        $this->scheme   = $parts['scheme'] ?? 'http';
        $this->host     = isset($parts['host']) ? strtolower($parts['host']) : 'localhost';
        $this->port     = $parts['port'] ?? static::SCHEME_PORTS[$this->scheme] ?? 80;
        $this->user     = $parts['user'] ?? null;
        $this->password = $parts['pass'] ?? null;
        $this->path     = isset($parts['path']) ? trim($parts['path']) : '/';
        $this->fragment = $parts['fragment'] ?? '';

        parse_str($parts['query'] ?? '', $this->query);
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority(): string
    {
        $authority = $this->host;

        if ($this->getUserInfo()) {
            $authority = "{$this->getUserInfo()}@{$authority}";
        }

        if ($this->port !== static::SCHEME_PORTS[$this->scheme]) {
            $authority .= ":{$this->port}";
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo(): string
    {
        $userInfo = $this->user;

        if (null !== $this->password) {
            $userInfo .= ":{$this->password}";
        }

        return $userInfo;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function getPort(): ?int
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return http_build_query($this->query);
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param $scheme
     * @return $this
     */
    public function withScheme($scheme): static
    {
        if ($scheme !== 'http' && $scheme !== 'https') {
            throw new InvalidArgumentException("Scheme $scheme not supported");
        }

        return $this->immutableInstance(['scheme' => $scheme]);
    }

    /**
     * @param $user
     * @param $password
     * @return $this
     */
    public function withUserInfo($user, $password = null): static
    {
        if ($user === $this->user && $password === $this->password) {
            return $this;
        }

        return $this->immutableInstance(['user' => $user, 'password' => $password]);
    }

    /**
     * @param $host
     * @return $this
     */
    public function withHost($host): static
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException("Invalid host: $host");
        }

        return $this->immutableInstance(['host' => $host]);
    }

    /**
     * @param $port
     * @return $this
     */
    public function withPort($port): static
    {
        if (is_int($port)) {
            throw new InvalidArgumentException("Invalid port: $port");
        }

        return $this->immutableInstance(['port' => $port]);
    }

    /**
     * @param $path
     * @return $this
     */
    public function withPath($path): static
    {
        if (is_string($path)) {
            throw new InvalidArgumentException("Invalid path: $path");
        }

        return $this->immutableInstance(['path' => $path]);
    }

    /**
     * @param $query
     * @return $this
     */
    public function withQuery($query): static
    {
        if (!is_string($query) && !is_array($query)) {
            throw new InvalidArgumentException("Invalid query");
        }

        if (is_array($query)) {
            $queryAr = $query;
        } elseif (is_string($query)) {
            parse_str($query, $queryAr);
        } else {
            throw new InvalidArgumentException("Invalid query provided");
        }

        return $this->immutableInstance(['query' => $queryAr]);
    }

    /**
     * @param $fragment
     * @return $this
     */
    public function withFragment($fragment): static
    {
        return $this->immutableInstance(['fragment' => $fragment]);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $uri = "{$this->scheme}:";

        if ($this->getAuthority()) {
            $uri .= "//{$this->getAuthority()}";
        }

        if ($this->path) {
            $uri .= $this->path;
        }

        if ($this->query) {
            $uri .= '?' . http_build_query($this->query);
        }

        if ($this->fragment) {
            $uri .= "#{$this->fragment}";
        }

        return $uri;
    }

    /**
     * @param array $properties
     * @return $this
     */
    protected function immutableInstance(array $properties): static
    {
        $diff = [];

        foreach ($properties as $name => $value) {
            if ($this->$name !== $value) {
                $diff[$name] = $value;
            }
        }

        if (!count($diff)) {
            return $this;
        }

        $clone = clone $this;

        foreach ($diff as $name => $value) {
            $clone->$name = $value;
        }

        return $clone;
    }
}