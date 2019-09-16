<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\FileStream;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;


class UploadedFile implements UploadedFileInterface
{
	/**
	 * File stream for contents.
	 *
	 * @var FileStream|null
	 */
	protected $stream;

	/**
	 * Name of file as client uploaded it.
	 *
	 * @var string
	 */
	protected $clientFilename;

	/**
	 * Media (mime) type of file.
	 *
	 * @var string
	 */
	protected $clientMediaType;

	/**
	 * Temporary file name as stored on disk.
	 *
	 * @var string
	 */
	protected $tempFilename;

	/**
	 * File size (in bytes).
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * Error code for upload.
	 *
	 * @var int
	 */
	protected $error;

	/**
	 * UploadedFile constructor.
	 *
	 * @param string $clientFilename
	 * @param string $clientMediaType
	 * @param string $tempFilename
	 * @param integer $size
	 * @param integer $error
	 */
	public function __construct(string $clientFilename, string $clientMediaType, string $tempFilename, int $size, int $error = UPLOAD_ERR_OK)
	{
		$this->clientFilename = $clientFilename;
		$this->clientMediaType = $clientMediaType;
		$this->tempFilename = $tempFilename;
		$this->size = $size;
		$this->error = $error;
	}

	/**
	 * Create an UploadedFile instance from a single $_FILES element.
	 *
	 * @param array $file
	 * @return UploadedFile
	 */
	public static function createFromGlobal(array $file): UploadedFile
	{
		return new static(
			$file['name'] ?? 'filename',
			$file['type'] ?? 'text/plain',
			$file['tmp_name'] ?? 'tmp_file',
			(int) ($file['size'] ?? 0),
			(int) ($file['error'] ?? UPLOAD_ERR_OK)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getStream()
	{
		if( empty($this->stream) ){

			if( !\file_exists($this->tempFilename) ){
				throw new RuntimeException("File not found.");
			}

			$fh = \fopen($this->tempFilename, "r");

			if( empty($fh) ){
				throw new RuntimeException("Could not open file for reading.");
			}

			$this->stream = new FileStream($fh);
		}

		return $this->stream;
	}

	/**
	 * @inheritDoc
	 * @return void
	 */
	public function moveTo($targetPath)
	{
		if( !\file_exists($this->tempFilename) ){
			throw new RuntimeException("File does not exist.");
		}

		if( \php_sapi_name() === 'cli' ){

			if( \rename($this->tempFilename, $targetPath) === false ){
				throw new RuntimeException("Failed to move uploaded file to {$targetPath}.");
			}

		} else {

			if( \is_uploaded_file($this->tempFilename) === false ){
				throw new RuntimeException("File is not an uploaded file.");
			}

			if( \move_uploaded_file($this->tempFilename, $targetPath) === false ){
				throw new RuntimeException("Failed to move uploaded file to {$targetPath}.");
			}

		}
	}

	/**
	 * @inheritDoc
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * @inheritDoc
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientFilename()
	{
		return $this->clientFilename;
	}

	/**
	 * @inheritDoc
	 */
	public function getClientMediaType()
	{
		return $this->clientMediaType;
	}
}