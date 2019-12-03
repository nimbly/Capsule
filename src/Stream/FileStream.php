<?php declare(strict_types=1);

namespace Capsule\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

class FileStream implements StreamInterface
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
     * @var resource
     */
    protected $resource;

    /**
     * FileStream constructor.
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
        \fclose($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function detach()
    {
		$this->close();
		return $this->resource;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        $fstat = \fstat($this->resource);
        return $fstat["size"] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
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
        return \feof($this->resource);
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return (bool) $this->getMetadata('seekable');
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if( \fseek($this->resource, $offset, $whence) !== 0 ){
			throw new RuntimeException("Could not seek file.");
		}
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        if( \rewind($this->resource) === false ){
			throw new RuntimeException("Could not rewind file.");
		}
    }

    /**
     * @inheritDoc
     */
    public function isWritable(): bool
    {
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
        $buffer = "";

        while( !$this->eof() ){
            $buffer .= $this->read(1024);
        }

        return $buffer;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        if( empty($key) ){
            return \stream_get_meta_data($this->resource);
        }

		$meta = \stream_get_meta_data($this->resource);

		return $meta[$key] ?? null;
    }
}