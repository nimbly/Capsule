<?php

namespace Nimbly\Capsule;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Nimbly\Capsule\Stream\BufferStream;
use UnexpectedValueException;

class Response extends MessageAbstract implements ResponseInterface
{
	protected int $statusCode;
	protected string $reasonPhrase;

	/**
	 * Response constructor.
	 *
	 * @param int|ResponseStatus $statusCode
	 * @param string|StreamInterface $body
	 * @param array<string,string> $headers
	 * @param string|null $reasonPhrase
	 * @param string $httpVersion
	 */
	public function __construct(
		int|ResponseStatus $statusCode,
		string|StreamInterface $body = null,
		array $headers = [],
		?string $reasonPhrase = null,
		string $httpVersion = "1.1")
	{
		if( \is_int($statusCode) ){
			$statusCode = ResponseStatus::from($statusCode);
		}

		$this->statusCode = $statusCode->value;
		$this->body = $body instanceof StreamInterface ? $body : new BufferStream((string) $body);
		$this->reasonPhrase = $reasonPhrase ?: $statusCode->getPhrase();
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
	 * @param int|ResponseStatus $code
	 * @param string $reasonPhrase
	 * @return static
	 */
	public function withStatus($code, $reasonPhrase = ""): Response
	{
		if( \is_int($code) ){
			$code = ResponseStatus::from($code);
		}
		elseif( $code instanceof ResponseStatus === false ){
			throw new UnexpectedValueException("Expecting either an integer or a ResponseStatus enum.");
		}

		$instance = clone $this;
		$instance->statusCode = $code->value;
		$instance->reasonPhrase = $reasonPhrase ? $reasonPhrase : $code->getPhrase();
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