<?php

namespace Nimbly\Capsule;

use Nimbly\Capsule\Stream\ResourceStream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
	protected StreamInterface $stream;

	/**
	 * Flag on whether file has already been moved.
	 *
	 * @var boolean
	 */
	private bool $file_moved = false;

	/**
	 * @param string|StreamInterface $stream StreamInterface instance or a string to the full path of the file.
	 * @param string|null $fileName
	 * @param string|null $mediaType
	 * @param integer|null $size
	 * @param integer $error
	 */
	public function __construct(
		string|StreamInterface $stream,
		protected ?string $fileName = null,
		protected ?string $mediaType = null,
		protected ?int $size = null,
		protected int $error = UPLOAD_ERR_OK)
	{
		if( \is_string($stream) ){
			$fh = \fopen($stream, "r");

			if( $fh === false ){
				throw new RuntimeException("Failed to open file for reading.");
			}

			$stream = new ResourceStream($fh);
		}

		$this->stream = $stream;
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

		if( $fh === false ){
			throw new RuntimeException("Failed to create target file or it cannot be written to.");
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