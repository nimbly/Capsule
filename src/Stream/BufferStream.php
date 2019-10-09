<?php declare(strict_types=1);

namespace Capsule\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;


class BufferStream implements StreamInterface
{
    /**
     * The buffer contents.
     *
     * @var string
     */
    protected $buffer = "";

    /**
     * BufferStream constructor.
     *
     * @param string $data
     */
    public function __construct($data = "")
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
		$this->close();
		return null;
    }

    /**
     * @inheritDoc
     */
    public function getSize(): ?int
    {
        return \strlen($this->buffer);
    }

    /**
     * @inheritDoc
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function eof(): bool
    {
        return (\strlen($this->buffer) === 0);
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
	 * @return void
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new RuntimeException("A BufferStream is not seekable.");
    }

    /**
     * @inheritDoc
     * @return void
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
        return true;
    }

    /**
     * @inheritDoc
     */
    public function write($string): int
    {
        $this->buffer .= $string;
        return \strlen($string);
    }

    /**
     * @inheritDoc
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function read($length): string
    {
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
        $buffer = $this->buffer;
        $this->buffer = "";
        return $buffer;
    }

    /**
     * @inheritDoc
     */
    public function getMetadata($key = null)
    {
        return null;
    }
}