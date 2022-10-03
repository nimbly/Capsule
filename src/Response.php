<?php

namespace Nimbly\Capsule;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Nimbly\Capsule\Stream\BufferStream;

class Response extends MessageAbstract implements ResponseInterface
{
	protected int $statusCode;
	protected string $reasonPhrase;

	/**
	 * Response constructor.
	 *
	 * @param int $statusCode
	 * @param string|StreamInterface $body
	 * @param array<string,string> $headers
	 * @param string|null $reasonPhrase
	 * @param string $http_version
	 */
	public function __construct(
		int $statusCode,
		string|StreamInterface $body = null,
		array $headers = [],
		?string $reasonPhrase = null,
		string $httpVersion = "1.1")
	{
		$this->statusCode = $statusCode;
		$this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);
		$this->reasonPhrase = $reasonPhrase ?: ResponseStatus::getPhrase($statusCode);
		$this->setHeaders($headers);
		$this->version = $httpVersion;
	}

	/**
	 * @inheritDoc
	 */
	public function getStatusCode(): int
	{
		return $this->statusCode;
	}

	/**
	 * @inheritDoc
	 * @param int $code
	 * @param string $reasonPhrase
	 * @return static
	 */
	public function withStatus($code, $reasonPhrase = ""): Response
	{
		$instance = clone $this;
		$instance->statusCode = $code;
		$instance->reasonPhrase = $reasonPhrase ?: ResponseStatus::getPhrase($code);
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