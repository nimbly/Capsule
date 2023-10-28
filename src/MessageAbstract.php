<?php

namespace Nimbly\Capsule;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * This abstract class provides common functionality between Request, ServerRequest, and Response
 * implementations.
 */
abstract class MessageAbstract implements MessageInterface
{
	/**
	 * Message version
	 *
	 * @var string
	 */
	protected string $version = "1.1";

	/**
	 * Message headers
	 *
	 * @var array<string,array<string>>
	 */
	protected array $headers = [];

	/**
	 * Message body
	 *
	 * @var StreamInterface
	 */
	protected StreamInterface $body;

	/**
	 * Allowed HTTP versions
	 *
	 * @var array<string>
	 */
	private array $allowedVersions = [
		"1", "1.0", "1.1", "2", "2.0"
	];

	/**
	 * @inheritDoc
	 * @return string
	 */
	public function getProtocolVersion(): string
	{
		return $this->version;
	}

	/**
	 * @inheritDoc
	 * @param string $version
	 * @return static
	 */
	public function withProtocolVersion($version): self
	{
		if( !\in_array($version, $this->allowedVersions) ){
			throw new RuntimeException("Invalid protocol version {$version}");
		}

		$instance = clone $this;
		$instance->version = $version;
		return $instance;
	}

	/**
	 * Find a header by its case-insensitive name.
	 *
	 * @param string $name
	 * @return string|null
	 */
	private function findHeaderKey(string $name): ?string
	{
		foreach( \array_keys($this->headers) as $header ){
			if( \strtolower($header) === \strtolower($name) ){
				return $header;
			}
		}

		return null;
	}

	/**
	 * @inheritDoc
	 * @return array<string,array<string>>
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 */
	public function hasHeader($name): bool
	{
		return ($this->findHeaderKey($name) !== null);
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 * @return array<string>
	 */
	public function getHeader($name): array
	{
		$key = $this->findHeaderKey($name);

		if( $key === null ){
			return [];
		}

		return $this->headers[$key];
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 */
	public function getHeaderLine($name): string
	{
		$header = $this->getHeader($name);

		if( empty($header) ){
			return "";
		}

		return \implode(",", $header);
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 * @param string|array<string> $value
	 * @return static
	 */
	public function withHeader($name, $value): static
	{
		$instance = clone $this;

		$key = $this->findHeaderKey($name);

		if( $key === null ){
			$key = $name;
		}

		if( !\is_array($value) ){
			$value = [$value];
		}

		$instance->headers[$key] = $value;
		return $instance;
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 * @param string|array<string> $value
	 * @return static
	 */
	public function withAddedHeader($name, $value): static
	{
		$key = $this->findHeaderKey($name);

		if( $key === null ){
			$key = $name;
		}

		$instance = clone $this;

		if( !\is_array($value) ){
			$value = [$value];
		}

		$instance->headers[$key] = \array_merge(
			$instance->headers[$key] ?? [],
			$value
		);

		return $instance;
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 * @return static
	 */
	public function withoutHeader($name): static
	{
		$key = $this->findHeaderKey($name);

		if( $key === null ){
			return $this;
		}

		$instance = clone $this;
		unset($instance->headers[$key]);
		return $instance;
	}

	/**
	 * Mass assign headers.
	 *
	 * @param array<string,string>|array<string,array<string>> $headers
	 * @throws RuntimeException
	 * @return void
	 */
	protected function setHeaders(array $headers): void
	{
		foreach( $headers as $name => $value ){

			if( !\is_array($value) ){
				$value = [$value];
			}

			$this->headers[$name] = $value;
		}
	}

	/**
	 * Set the Host header.
	 *
	 * @param string $host
	 * @param int|null $port
	 * @return void
	 */
	protected function setHostHeader(string $host, ?int $port = null): void
	{
		if( ($key = $this->findHeaderKey("Host")) ){
			unset($this->headers[$key]);
		}

		if( $port ){
			$host .= ":{$port}";
		}

		$this->headers = \array_merge(
			["Host" => [$host]],
			$this->headers
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getBody(): StreamInterface
	{
		return $this->body;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withBody(StreamInterface $body): static
	{
		$instance = clone $this;
		$instance->body = $body;
		return $instance;
	}
}