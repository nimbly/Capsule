<?php declare(strict_types=1);

namespace Capsule\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class ResourceStream implements StreamInterface
{
    /**
     * Array of file modes broken into readable and writeable.
     *
     * @var array
     */
    private $fileModes = [
        "readable" => [
            "r", "r+", "w+", "a+", "x+", "c+",
            "rb", "r+b", "w+b", "a+b", "x+b", "c+b",
            "rt", "r+t", "w+t", "a+t", "x+t", "c+t",
        ],

        "writeable" => [
            "w", "w+", "r+", "a", "a+", "x", "x+", "c", "c+",
            "wb", "w+b", "r+b", "ab", "a+b", "xb", "x+b", "cb", "c+b",
            "wt", "w+t", "r+t", "at", "a+t", "xt", "x+t", "ct", "c+t",
        ],
    ];

    /**
     * Stream resource.
     *
     * @var resource|null
     */
    protected $resource;

    /**
     * ResourceStream constructor.
     *
     * @param resource $resource
     */
    public function __construct($resource)
    {
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
		if( !empty($this->resource) ){
			\fclose($this->resource);
			$this->resource = null;
		}
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
		$resource = $this->resource;
		$this->resource = null;

		return $resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
		if( empty($this->resource) ){
			return null;
		}

        $fstat = \fstat($this->resource);
        return $fstat["size"] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
		if( empty($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

        $position = \ftell($this->resource);

        if( $position === false ){
			throw new RuntimeException("Could not tell position in file.");
		}

		return $position;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
		if( empty($this->resource) ){
			return true;
		}

        return \feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
		if( empty($this->resource) ){
			return false;
		}

        return (bool) $this->getMetadata('seekable');
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
		if( empty($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

        if( \fseek($this->resource, $offset, $whence) !== 0 ){
			throw new RuntimeException("Could not seek file.");
		}
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
		if( empty($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

        if( \rewind($this->resource) === false ){
			throw new RuntimeException("Could not rewind file.");
		}
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
		if( empty($this->resource) ){
			return false;
		}

		/** @psalm-suppress PossiblyInvalidCast */
		$mode = (string) $this->getMetadata('mode');

        return \in_array(
			\strtolower($mode),
			$this->fileModes['writeable'])
		;
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
		if( empty($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

        $bytes = \fwrite($this->resource, $string);

        if( $bytes === false ){
			throw new RuntimeException("Could not write to file.");
		}

		return $bytes;
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
		if( empty($this->resource) ){
			return false;
		}

		/** @psalm-suppress PossiblyInvalidCast */
		$mode = (string) $this->getMetadata('mode');

        return \in_array(
			\strtolower($mode),
			$this->fileModes['readable']
		);
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
		if( empty($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

        $data = \fread($this->resource, $length);

        if( $data === false ){
			throw new RuntimeException("Could not read from file.");
		}

		return $data;
    }

    /**
     * @inheritDoc
     */
    public function getContents(): string
    {
		if( empty($this->resource) ){
			throw new RuntimeException("Underlying resource has been detached.");
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
    public function getMetadata($key = null)
    {
		if( empty($this->resource) ){
			return null;
		}

        if( empty($key) ){
            return \stream_get_meta_data($this->resource);
        }

		$meta = \stream_get_meta_data($this->resource);

		return $meta[$key] ?? null;
    }
}