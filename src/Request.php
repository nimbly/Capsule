<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\BufferStream;
use Capsule\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;


class Request extends MessageAbstract implements RequestInterface
{
    /**
     * HTTP method
     *
     * @var string
     */
    protected $method;

    /**
     * Request URI
     *
     * @var UriInterface
     */
    protected $uri;

    /**
     * Request target form
     *
     * @var string
     */
    protected $requestTarget;

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param StreamInterface|string $body
     * @param array<string, string> $headers
     * @param string $httpVersion
     */
    public function __construct(string $method, $uri, $body = null, array $headers = [], string $httpVersion = "1.1")
    {
        $this->method = \strtoupper((string) $method);
        $this->uri = $uri instanceof UriInterface ? $uri : Uri::createFromString((string) $uri);
        $this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);

		$this->setHeaders($headers);

		if( $this->uri->getHost() ){
			$this->setHostHeader($this->uri->getHost(), $this->uri->getPort());
		}

        $this->version = $httpVersion;
	}

    /**
     * @inheritDoc
     */
    public function withMethod($method) : Request
    {
        $instance = clone $this;
        $instance->method = \strtoupper($method);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        $instance = clone $this;
		$instance->uri = $uri;

		if( $preserveHost === false ||
			$this->hasHeader('Host') === false ){
			$instance->setHostHeader($uri->getHost(), $uri->getPort());
		}

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getRequestTarget()
    {
		if( !empty($this->requestTarget) ){
			return $this->requestTarget;
		}

		$requestTarget = $this->uri->getPath();

		if( empty($requestTarget) ){
			$requestTarget = "/";
		}

		if( !empty($this->uri->getQuery()) ){
			$requestTarget .= "?" . $this->uri->getQuery();
		}

        return $requestTarget;
    }

    /**
     * @inheritDoc
     */
    public function withRequestTarget($requestTarget)
    {
        $instance = clone $this;
        $instance->requestTarget = $requestTarget;
        return $instance;
    }
}