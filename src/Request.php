<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Factory\UriFactory;
use Capsule\Stream\BufferStream;
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
	 * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $requestTarget;

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param StreamInterface|string|null $body
     * @param array<string,string> $headers
     * @param string $httpVersion
     */
    public function __construct(string $method, $uri, $body = null, array $headers = [], string $httpVersion = "1.1")
    {
        $this->method = \strtoupper($method);
        $this->uri = $uri instanceof UriInterface ? $uri : UriFactory::createFromString($uri);
        $this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);

		$this->setHeaders($headers);

		if( $this->uri->getHost() ){
			$this->setHostHeader($this->uri->getHost(), $this->uri->getPort());
		}

        $this->version = $httpVersion;
	}

    /**
     * @inheritDoc
	 * @param string $method
	 * @return static
     */
    public function withMethod($method): Request
    {
        $instance = clone $this;
        $instance->method = \strtoupper($method);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritDoc
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @inheritDoc
	 * @param UriInterface $uri
	 * @param bool $preserveHost
	 * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false): Request
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
    public function getRequestTarget(): string
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
	 * @param mixed $requestTarget
	 * @return static
     */
    public function withRequestTarget($requestTarget): Request
    {
        $instance = clone $this;
        $instance->requestTarget = $requestTarget;
        return $instance;
    }
}