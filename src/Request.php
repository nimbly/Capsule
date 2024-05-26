<?php

namespace Nimbly\Capsule;

use Nimbly\Capsule\Factory\StreamFactory;
use Nimbly\Capsule\Factory\UriFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * The `Request` class represents an outgoing HTTP request to another service, typically to be used by
 * a PSR-18 compliant HTTP client library.
 */
class Request extends MessageAbstract implements RequestInterface
{
	/**
	 * HTTP method
	 *
	 * @var string
	 */
	protected string $method;

	/**
	 * Request URI
	 *
	 * @var UriInterface
	 */
	protected UriInterface $uri;

	/**
	 * Request target form
	 *
	 * @var string|null
	 */
	protected ?string $requestTarget = null;

	/**
	 * @param string $method The HTTP method to use for the request. For example, "POST", "GET", etc.
	 * @param string|UriInterface $uri The URI of the resource you are trying to call. For example: "https://api.example.com/books/12345"
	 * @param string|StreamInterface|null $body The body of the request. If request does not contain a body, you can use a null or empty string value.
	 * @param array<string,string> $headers An array of key & value pairs for headers to be included in the request. For example, ["Content-Type" => "application/json"]
	 * @param string $httpVersion The HTTP protocol version to use for this request. Defaults to "1.1".
	 */
	public function __construct(
		string $method,
		string|UriInterface $uri,
		string|StreamInterface|null $body = null,
		array $headers = [],
		string $httpVersion = "1.1")
	{
		$this->method = \strtoupper($method);
		$this->uri = $uri instanceof UriInterface ? $uri : UriFactory::createFromString($uri);
		$this->body = $body instanceof StreamInterface ? $body : StreamFactory::createFromString((string) $body);

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
	public function withMethod($method): static
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
	public function withUri(UriInterface $uri, $preserveHost = false): static
	{
		$instance = clone $this;
		$instance->uri = $uri;

		if( $preserveHost === false ||
			$this->hasHeader("Host") === false ){
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
	public function withRequestTarget($requestTarget): static
	{
		$instance = clone $this;
		$instance->requestTarget = (string) $requestTarget;
		return $instance;
	}
}