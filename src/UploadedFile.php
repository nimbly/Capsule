<?php

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
	 * Error code for upload.
	 *
	 * @var int
	 */
	protected $error;

	/**
	 * Create an UploadedFile instance from a PHP $_FILES element.
	 *
	 * @param array $file
	 * @return UploadedFile
	 */
	public static function createFromGlobal(array $file): UploadedFile
	{
		$uploadedFile = new static;
		$uploadedFile->clientFilename = $file['name'] ?? 'filename';
		$uploadedFile->clientMediaType = $file['type'] ?? 'text/plain';
		$uploadedFile->size = (int) ($file['size'] ?? 0);
		$uploadedFile->tempFilename = $file['tmp_name'] ?? 'tmp_file';
		$uploadedFile->error = (int) ($file['error'] ?? 0);
		return $uploadedFile;
	}

	/**
	 * @inheritDoc
	 */
	public function getStream()
	{
		if( empty($this->stream) ){
			$this->stream = new FileStream(
				\fopen($this->tempFilename)
			);
		}

		return $this->stream;
	}

	/**
	 * @inheritDoc
	 */
	public function moveTo($destination)
	{
		if( \move_uploaded_file($this->tempFilename, $destination) === false ){
			throw new RuntimeException("Failed to move uploaded file to {$destination}.");
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