<?php

namespace Nimbly\Capsule;

use Nimbly\Capsule\Factory\StreamFactory;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * The UploadedFile class represents a single uploaded file within a ServerRequest instance.
 */
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
	 * @param string|StreamInterface $stream StreamInterface instance of the file contents or a string to the full path of the file. For example: "/tmp/upload_aa38ajbak189"
	 * @param string|null $fileName Name of the file. For example: "weekly_report.csv"
	 * @param string|null $mediaType The media or mime type of the file. For example: "image/png".
	 * @param integer|null $size The size of the file in bytes. For example: 1048576.
	 * @param integer $error The PHP UPLOAD_ERR_* error code for the file upload.
	 */
	public function __construct(
		string|StreamInterface $stream,
		protected ?string $fileName = null,
		protected ?string $mediaType = null,
		protected ?int $size = null,
		protected int $error = UPLOAD_ERR_OK)
	{
		if( \is_string($stream) ){
			$stream = StreamFactory::createFromFile($stream, "r");
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

		$targetStream = StreamFactory::createFromFile($targetPath, "w+");

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