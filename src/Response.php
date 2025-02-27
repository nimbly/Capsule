<?php

namespace Nimbly\Capsule;

use Nimbly\Capsule\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * The `Response` class represents a response to an HTTP request and can be used for both `Request` and `ServerRequest` instances.
 */
class Response extends MessageAbstract implements ResponseInterface
{
	protected ResponseStatus $statusCode;
	protected string $reasonPhrase;

	/**
	 * @param int|ResponseStatus $statusCode The HTTP response status code. For example: 200, 404, etc. Alternatively, you can use the ResponseStatus enum.
	 * @param string|StreamInterface|null $body The body of the response. If no body is expected for the response, you can use a null or empty string value.
	 * @param array<string,string> $headers An array of key & value pairs for headers to be included in the response. For example, ["Content-Type" => "application/json"]
	 * @param string|null $reasonPhrase The HTTP status code reason phrase. For example, "Not Found" for 404. By default, the reason phrases listed in the ResponseStatus enum will be used if none provided.
	 * @param string $http_version The HTTP protocol version of the response. Defaults to "1.1".
	 */
	public function __construct(
		int|ResponseStatus $statusCode,
		string|StreamInterface|null $body = null,
		array $headers = [],
		?string $reasonPhrase = null,
		string $httpVersion = "1.1")
	{
		$this->statusCode = \is_int($statusCode) ? ResponseStatus::from($statusCode) : $statusCode;
		$this->body = $body instanceof StreamInterface ? $body : StreamFactory::createFromString((string) $body);
		$this->setHeaders($headers);
		$this->reasonPhrase = $reasonPhrase ?: $this->statusCode->getPhrase();
		$this->version = $httpVersion;
	}

	/**
	 * @inheritDoc
	 */
	public function getStatusCode(): int
	{
		return $this->statusCode->value;
	}

	/**
	 * @inheritDoc
	 * @param int $code
	 * @param string $reasonPhrase
	 * @return static
	 */
	public function withStatus($code, $reasonPhrase = ""): static
	{
		$instance = clone $this;
		$instance->statusCode = ResponseStatus::from($code);
		$instance->reasonPhrase = $reasonPhrase ?: $instance->statusCode->getPhrase();
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getReasonPhrase(): string
	{
		return $this->reasonPhrase;
	}
}