<?php declare(strict_types=1);

namespace Capsule\Stream;

use InvalidArgumentException;
use OutOfBoundsException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class BufferStream implements StreamInterface
{
	/**
	 * The buffer contents.
	 *
	 * When buffer is *null*, the stream has been "detached."
	 *
	 * @var string|null
	 */
	protected $buffer = "";

	/**
	 * Buffer position.
	 *
	 * @var integer
	 */
	protected $position = 0;

	/**
	 * BufferStream constructor.
	 *
	 * @param string $data
	 */
	public function __construct(string $data = "")
	{
		$this->buffer = $data;
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
		$this->buffer = "";
		return;
	}

	/**
	 * @inheritDoc
	 */
	public function detach()
	{
		$this->buffer = null;
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getSize(): ?int
	{
		if( $this->buffer === null ){
			return null;
		}

		return \strlen($this->buffer);
	}

	/**
	 * @inheritDoc
	 */
	public function tell(): int
	{
		return $this->position;
	}

	/**
	 * @inheritDoc
	 */
	public function eof(): bool
	{
		return $this->buffer === null || $this->position === \strlen($this->buffer);
	}

	/**
	 * @inheritDoc
	 */
	public function isSeekable(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function seek($offset, $whence = SEEK_SET): void
	{
		if( $this->buffer === null ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

		switch( $whence ){
			case SEEK_SET:
				if( $offset < 0 || $offset > \strlen($this->buffer) ){
					throw new OutOfBoundsException("Offset is out of bounds");
				}

				$this->position = $offset;
			break;

			case SEEK_CUR:
				$position = $offset + $this->position;

				if( $position < 0 || $position > \strlen($this->buffer) ){
					throw new OutOfBoundsException("Offset is out of bounds");
				}

				$this->position = $position;
			break;

			case SEEK_END:
				$position = \strlen($this->buffer) - $offset;

				if( $position < 0 || $position > \strlen($this->buffer) ){
					throw new OutOfBoundsException("Offset is out of bounds");
				}

				$this->position = $position;
			break;

			default:
				throw new InvalidArgumentException("Invalid seek position");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function rewind(): void
	{
		$this->seek(0);
	}

	/**
	 * @inheritDoc
	 */
	public function isWritable(): bool
	{
		if( $this->buffer === null ){
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function write($string): int
	{
		if( $this->buffer === null ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

		$this->buffer .= $string;
		return \strlen($string);
	}

	/**
	 * @inheritDoc
	 */
	public function isReadable(): bool
	{
		if( $this->buffer === null ){
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function read($length): string
	{
		if( $this->buffer === null ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

		if( $length > (\strlen($this->buffer) - $this->position) ){
			$length = \strlen($this->buffer) - $this->position;
		}

		$chunk = \substr($this->buffer, $this->position, $length);
		$this->position += $length;
		return $chunk;
	}

	/**
	 * @inheritDoc
	 */
	public function getContents(): string
	{
		if( $this->buffer === null ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

		return $this->buffer;
	}

	/**
	 * @inheritDoc
	 */
	public function getMetadata($key = null)
	{
		if( $key ){
			return null;
		}

		return [];
	}
}