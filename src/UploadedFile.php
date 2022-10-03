<?php

namespace Nimbly\Capsule;

use Nimbly\Capsule\Stream\ResourceStream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
	/**
	 * Flag on whether file has already been moved.
	 *
	 * @var boolean
	 */
	private bool $file_moved = false;

	/**
	 * UploadedFile constructor.
	 *
	 * @param StreamInterface $stream
	 * @param string|null $fileName
	 * @param string|null $mediaType
	 * @param integer|null $size
	 * @param integer $error
	 */
	public function __construct(
		protected StreamInterface $stream,
		protected ?string $fileName = null,
		protected ?string $mediaType = null,
		protected ?int $size = null,
		protected int $error = UPLOAD_ERR_OK)
	{
	}

	/**
	 * Validate the underlying file/stream is in a state to be operated on .
	 *
	 * @throws RuntimeException
	 * @return void
	 */
	private function validateStream(): void
	{
		if( $this->error !== UPLOAD_ERR_OK || $this->file_moved ){
			throw new RuntimeException("Underlying stream is not operable.");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getStream(): StreamInterface
	{
		$this->validateStream();
		return $this->stream;
	}

	/**
	 * @inheritDoc
	 * @param string $targetPath
	 * @return void
	 */
	public function moveTo($targetPath): void
	{
		$this->validateStream();

		if( empty($targetPath) ){
			throw new RuntimeException("Target file cannot be empty.");
		}

		$fh = \fopen($targetPath, "w+");

		if( empty($fh) ){
			throw new RuntimeException("Target file cannot be written to.");
		}

		$targetStream = new ResourceStream($fh);

		while( !$this->stream->eof() ){
			$targetStream->write(
				$this->stream->read(8192)
			);
		}

		$this->file_moved = true;
	}

	/**
	 * @inheritDoc
	 */
	public function getSize(): ?int
	{
		return $this->size;
	}

	/**
	 * @inheritDoc
	 */
	public function getError(): int
	{
		return $this->error;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientFilename(): ?string
	{
		return $this->fileName;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientMediaType(): ?string
	{
		return $this->mediaType;
	}
}