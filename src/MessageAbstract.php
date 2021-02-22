<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;


abstract class MessageAbstract implements MessageInterface
{
	/**
	 * Message version
	 *
	 * @var string
	 */
	protected $version = "1.1";

	/**
	 * Message headers
	 *
	 * @var array<string,array<string>>
	 */
	protected $headers = [];

	/**
	 * Message body
	 *
	 * @var StreamInterface
	 */
	protected $body;

	/**
	 * Allowed HTTP versions
	 *
	 * @var array<string>
	 */
	private $allowedVersions = [
		"1.1", "1.0", "2", "2.0"
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
	 */
	private function findHeaderKey(string $name): ?string
	{
		foreach( $this->headers as $key => $value ){
			if( \strtolower($name) === \strtolower($key) ){
				return $key;
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
		if( ($key = $this->findHeaderKey($name)) !== null ){
			return $this->headers[$key];
		}

		return [];
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
	public function withHeader($name, $value): self
	{
		$instance = clone $this;

		if( ($key = $this->findHeaderKey($name)) === null ){
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
	public function withAddedHeader($name, $value): self
	{
		if( ($key = $this->findHeaderKey($name)) === null ){
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
	public function withoutHeader($name): self
	{
		if( ($key = $this->findHeaderKey($name)) === null ){
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
		if( ($key = $this->findHeaderKey('Host')) ){
			unset($this->headers[$key]);
		}

		if( $port ){
			$host .= ":{$port}";
		}

		$this->headers = \array_merge(
			['Host' => [$host]],
			$this->headers
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getBody(): ?StreamInterface
	{
		return $this->body;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withBody(StreamInterface $body): self
	{
		$instance = clone $this;
		$instance->body = $body;
		return $instance;
	}
}