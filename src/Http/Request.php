<?php

namespace Solar\MicroFramework\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Solar\Microframework\Trait\CloneWithTrait;

class Request implements RequestInterface
{
    use CloneWithTrait;
    use MessageTrait;

    const VALID_METHODS = [
        'delete',
        'get',
        'head',
        'options',
        'patch',
        'post',
        'put'
    ];

    /**
     * @var string
     */
    protected string $method;

    /**
     * @var string
     */
    protected string $requestTarget;

    /**
     * @var UriInterface
     */
    protected UriInterface $uri;

    public function __construct(
        string $method,
        $uri,
        array $headers = [],
        StreamInterface $body = null,
        string $protocol = '1.1'
    ) {
        $this->method = mb_strtolower($method);
        $this->uri = is_string($uri) ? new Uri($uri) : $uri;
        $this->setHeaders($headers);
        $this->setBody($body);
        $this->protocol = $protocol;
    }

    /**
     * @return string
     */
    public function getRequestTarget(): string
    {
        return $this->requestTarget;
    }

    /**
     * @param string $requestTarget
     * @return $this
     */
    public function withRequestTarget($requestTarget): self
    {
        return $this->cloneWith(['requestTarget' => $requestTarget]);
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return $this
     */
    public function withMethod($method): self
    {
        $this->validateMethod($method);

        return $this->cloneWith(['method' => $method]);
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return Request
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if (!$preserveHost) {
            $uri = $uri->withHost($this->uri->getHost());
        }

        return $this->cloneWith(['uri' => $uri]);
    }


}