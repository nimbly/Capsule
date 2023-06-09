<?php

namespace Nimbly\Capsule;

use Nimbly\Capsule\Factory\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends MessageAbstract implements ResponseInterface
{
	protected ResponseStatus $statusCode;
	protected ?string $reasonPhrase;

	/**
	 * @param int|ResponseStatus $statusCode
	 * @param string|StreamInterface $body
	 * @param array<string,string> $headers
	 * @param string|null $reasonPhrase
	 * @param string $http_version
	 */
	public function __construct(
		int|ResponseStatus $statusCode,
		string|StreamInterface $body = null,
		array $headers = [],
		?string $reasonPhrase = null,
		string $httpVersion = "1.1")
	{
		$this->statusCode = \is_int($statusCode) ? ResponseStatus::from($statusCode) : $statusCode;
		$this->body = $body instanceof StreamInterface ? $body : StreamFactory::createFromString((string) $body);
		$this->setHeaders($headers);
		$this->reasonPhrase = $reasonPhrase;
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
		$instance->reasonPhrase = $reasonPhrase ?: null;
		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getReasonPhrase(): string
	{
		return $this->reasonPhrase ?: $this->statusCode->getPhrase();
	}
}