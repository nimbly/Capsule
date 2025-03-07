<?php

namespace Nimbly\Capsule\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * A ResourceStream supports *any* streamable PHP resource.
 */
class ResourceStream implements StreamInterface
{
	/**
	 * Array of file modes broken into readable and writeable.
	 *
	 * @var array<string,array<string>>
	 */
	private array $fileModes = [
		"readable" => [
			"r", "r+", "w+", "a+", "x+", "c+",
			"rb", "r+b", "w+b", "a+b", "x+b", "c+b",
			"rt", "r+t", "w+t", "a+t", "x+t", "c+t",
		],

		"writeable" => [
			"w", "w+", "r+", "a", "a+", "x", "x+", "c", "c+",
			"wb", "w+b", "r+b", "ab", "a+b", "xb", "x+b", "cb", "c+b",
			"wt", "w+t", "r+t", "at", "a+t", "xt", "x+t", "ct", "c+t",
		]
	];

	/**
	 * Stream resource.
	 *
	 * @var resource|closed-resource|null
	 */
	protected $resource;

	/**
	 * @param resource $resource Resource *must* be of PHP type "stream." For example: $resource = \fopen("/tmp/aa981naai1", "r");
	 */
	public function __construct($resource)
	{
		/**
		 * @psalm-suppress RedundantConditionGivenDocblockType
		 * @psalm-suppress DocblockTypeContradiction
		 */
		if( !\is_resource($resource) ||
			\get_resource_type($resource) !== "stream" ){
			throw new RuntimeException("Invalid resource supplied in constructor.");
		}

		$this->resource = $resource;
	}

	/**
	 * @inheritDoc
	 */
	public function __toString(): string
	{
		return $this->getContents();
	}

	/**
	 * @inheritDoc
	 */
	public function close(): void
	{
		if( \is_resource($this->resource) ){
			\fclose($this->resource);
			$this->resource = null;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function detach()
	{
		if( !\is_resource($this->resource) ){
			return null;
		}

		$resource = $this->resource;
		$this->resource = null;

		return $resource;
	}

	/**
	 * @inheritDoc
	 */
	public function getSize(): ?int
	{
		if( !\is_resource($this->resource) ){
			return null;
		}

		$fstat = \fstat($this->resource);

		if( $fstat === false ){
			return null;
		}

		if( !\array_key_exists("size", $fstat) ){
			return null;
		}

		return $fstat["size"];
	}

	/**
	 * @inheritDoc
	 */
	public function tell(): int
	{
		if( !\is_resource($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

		$position = \ftell($this->resource);

		if( $position === false ){
			throw new RuntimeException("Could not tell position in resource.");
		}

		return $position;
	}

	/**
	 * @inheritDoc
	 */
	public function eof(): bool
	{
		if( !\is_resource($this->resource) ){
			return true;
		}

		return \feof($this->resource);
	}

	/**
	 * @inheritDoc
	 */
	public function isSeekable(): bool
	{
		return (bool) $this->getMetadata("seekable");
	}

	/**
	 * @inheritDoc
	 */
	public function seek($offset, $whence = SEEK_SET): void
	{
		if( !\is_resource($this->resource) ||
			!$this->isSeekable() ){
			throw new RuntimeException("Resource is not seekable.");
		}

		if( \fseek($this->resource, $offset, $whence) !== 0 ){
			throw new RuntimeException("Could not seek resource.");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void
	{
		if( !\is_resource($this->resource) ||
			!$this->isSeekable() ){
			throw new RuntimeException("Resource is not seekable.");
		}

		if( \rewind($this->resource) === false ){
			throw new RuntimeException("Could not rewind resource.");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function isWritable(): bool
	{
		/** @psalm-suppress PossiblyInvalidCast */
		$mode = (string) $this->getMetadata("mode");

		return \in_array(
			\strtolower($mode),
			$this->fileModes["writeable"]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function write($string): int
	{
		if( !\is_resource($this->resource) ||
			!$this->isWritable() ){
			throw new RuntimeException("Resource is not writable.");
		}

		$bytes = \fwrite($this->resource, $string);

		if( $bytes === false ){
			throw new RuntimeException("Could not write to resource.");
		}

		return $bytes;
	}

	/**
	 * @inheritDoc
	 */
	public function isReadable(): bool
	{
		/** @psalm-suppress PossiblyInvalidCast */
		$mode = (string) $this->getMetadata("mode");

		return \in_array(
			\strtolower($mode),
			$this->fileModes["readable"]
		);
	}

	/**
	 * @inheritDoc
	 */
	public function read($length): string
	{
		if( !\is_resource($this->resource) ||
			!$this->isReadable() ){
			throw new RuntimeException("Resource is not readable.");
		}

		$data = \fread($this->resource, $length);

		if( $data === false ){
			throw new RuntimeException("Could not read from resource.");
		}

		return $data;
	}

	/**
	 * @inheritDoc
	 */
	public function getContents(): string
	{
		if( !\is_resource($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

		if( $this->isSeekable() ) {
			$this->rewind();
		}

		$contents = \stream_get_contents($this->resource);

		if( $contents === false ){
			throw new RuntimeException("Cannot read from stream.");
		}

		return $contents;
	}

	/**
	 * @inheritDoc
	 */
	public function getMetadata($key = null): mixed
	{
		if( !\is_resource($this->resource) ){
			return $key ? null : [];
		}

		$meta = \stream_get_meta_data($this->resource);

		if( empty($key) ){
			return $meta;
		}

		return $meta[$key] ?? null;
	}
}