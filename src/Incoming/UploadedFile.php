<?php

namespace Capsule\Incoming;

use Capsule\Stream\FileStream;


class UploadedFile
{
	/**
	 * The name of the file.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The mime type of the file.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * The size (in bytes) of the file.
	 *
	 * @var int
	 */
	protected $size;

	/**
	 * The temporary name on disk where file is stored.
	 *
	 * @var string
	 */
	protected $tmp_name;

	/**
	 * Whether there is an error with this file.
	 *
	 * @var bool
	 */
	protected $error;

	/**
	 * FileStream for uploaded file.
	 *
	 * @var FileStream|null
	 */
	private $stream;

	/**
	 * Create an UploadedFile instance.
	 *
	 * @param array $file
	 * @return UploadedFile
	 */
	public static function createFromGlobal(array $file): UploadedFile
	{
		$uploadedFile = new static;
		$uploadedFile->name = $file['name'] ?? 'filename';
		$uploadedFile->type = $file['type'] ?? 'text/plain';
		$uploadedFile->size = $file['size'] ?? 0;
		$uploadedFile->tmp_name = $file['tmp_name'] ?? 'tmp_file';
		$uploadedFile->error = (bool) ($file['error'] ?? false);
		return $uploadedFile;
	}

	/**
	 * Get the name of the UploadedFile.
	 *
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get the type of the UploadedFile.
	 *
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get the size of the UploadedFile.
	 *
	 * @return integer
	 */
	public function getSize(): int
	{
		return $this->size;
	}

	/**
	 * Get the temp file location on disk for the UploadedFile.
	 *
	 * @return string
	 */
	public function getTempLocation(): string
	{
		return $this->tmp_name;
	}

	/**
	 * Read entire contents of file into string.
	 *
	 * @return string
	 */
	public function getContents(): string
	{
		return \file_get_contents($this->tmp_name);
	}

	/**
	 * Get a FileStream instance for the uploaded file.
	 *
	 * @return FileStream
	 */
	public function getStream(): FileStream
	{
		if( empty($this->stream) ){
			$this->stream = new FileStream(
				\fopen($this->tmp_name)
			);
		}

		return $this->stream;
	}

	/**
	 * Move the uploaded file to given destination.
	 *
	 * @param string $destination
	 * @return boolean
	 */
	public function moveTo(string $destination): bool
	{
		return \move_uploaded_file($this->tmp_name, $destination);
	}
}