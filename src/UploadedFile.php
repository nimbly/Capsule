<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\ResourceStream;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;


class UploadedFile implements UploadedFileInterface
{
	/**
	 * Stream for contents.
	 *
	 * @var StreamInterface|null
	 */
	protected $stream;

	/**
	 * Path to file on disk.
	 *
	 * @var string|null
	 */
	protected $file;

	/**
	 * Name of file as client uploaded it.
	 *
	 * @var string|null
	 */
	protected $clientFilename;

	/**
	 * Media (mime) type of file.
	 *
	 * @var string|null
	 */
	protected $clientMediaType;

	/**
	 * File size (in bytes).
	 *
	 * @var int|null
	 */
	protected $size;

	/**
	 * Error code for upload.
	 *
	 * @var int
	 */
	protected $error;

	/**
	 * Flag on whether file has already been moved.
	 *
	 * @var boolean
	 */
	private $fileMoved = false;

	/**
	 * UploadedFile constructor.
	 *
	 * @param StreamInterface|string $contents
	 * @param string|null $clientFilename
	 * @param string|null $clientMediaType
	 * @param integer|null $size
	 * @param integer $error
	 */
	public function __construct($contents, ?string $clientFilename = null, ?string $clientMediaType = null, ?int $size = null, int $error = UPLOAD_ERR_OK)
	{
		/**
		 * @psalm-suppress RedundantConditionGivenDocblockType
		 */
		if( $contents instanceof StreamInterface ){
			$this->stream = $contents;
		}
		elseif( \is_string($contents) ){
			$this->file = $contents;
		}
		else {
			throw new RuntimeException("UploadedFile contents must either be a StreamInterface instance or a path to a file.");
		}

		$this->clientFilename = $clientFilename;
		$this->clientMediaType = $clientMediaType;
		$this->size = $size;
		$this->error = $error;
	}

	/**
	 * Validate the underlying file/stream is in a state to be operated on .
	 *
	 * @throws RuntimeException
	 * @return void
	 */
	private function validateStream(): void
	{
		if( $this->error !== UPLOAD_ERR_OK || $this->fileMoved ){
			throw new RuntimeException("Underlying stream is not operable.");
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getStream(): StreamInterface
	{
		$this->validateStream();

		if( $this->stream ){
			return $this->stream;
		}

		if( empty($this->file) ){
			throw new RuntimeException("Cannot open file for streaming.");
		}

		$this->stream = new ResourceStream(
			\fopen($this->file, "r")
		);

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

		if( $this->file ){

			$this->fileMoved = \php_sapi_name() == 'cli' ?
				\rename($this->file, $targetPath) :
				\move_uploaded_file($this->file, $targetPath);

		}
		elseif( $this->stream ) {

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
		}
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
		return $this->clientFilename;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientMediaType(): ?string
	{
		return $this->clientMediaType;
	}
}