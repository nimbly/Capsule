<?php declare(strict_types=1);

namespace Capsule\Stream;

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
		if( $this->buffer === null ){
			throw new RuntimeException("Underlying resource has been detached.");
		}

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return $this->buffer === null || \strlen($this->buffer) === 0;
    }

    /**
     * @inheritDoc
     */
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new RuntimeException("A BufferStream is not seekable.");
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

        if( $length >= \strlen($this->buffer) ){
            return $this->getContents();
        }

        $chunk = \substr($this->buffer, 0, $length);
        $this->buffer = \substr($this->buffer, $length);
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

        $buffer = $this->buffer;
        $this->buffer = "";
        return $buffer;
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