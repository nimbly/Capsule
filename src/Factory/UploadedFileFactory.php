<?php

namespace Nimbly\Capsule\Factory;

use InvalidArgumentException;
use Nimbly\Capsule\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createUploadedFile(
        StreamInterface $stream,
        int $size = null,
        int $error = UPLOAD_ERR_OK,
        string $clientFilename = null,
        string $clientMediaType = null
    ): UploadedFileInterface {

		return new UploadedFile(
			$stream,
			$clientFilename,
			$clientMediaType,
			$size,
			$error
		);
	}

	/**
	 * Create an UploadedFile instance from a single $_FILES element.
	 *
	 * @param array{tmp_name:string,name:string,type:string,size:int,error:int} $file
	 * @throws RuntimeException
	 * @return UploadedFile
	 */
	public static function createFromGlobal(array $file): UploadedFile
	{
		return new UploadedFile(
			StreamFactory::createFromFile($file["tmp_name"], "r"),
			$file["name"] ?? null,
			$file["type"] ?? null,
			$file["size"] ?? 0,
			$file["error"] ?? UPLOAD_ERR_OK
		);
	}

	/**
	 * Create an array of UploadedFile instances from the PHP $_FILES global.
	 *
	 * The $_FILES super global can take on several different shapes, especially
	 * if multiple files are being uploaded, and can be nested.
	 *
	 * @param array<string,mixed> $files
	 * @throws InvalidArgumentException For unrecognized values.
	 * @return array<UploadedFile>
	 */
	public static function createFromGlobals(array $files): array
	{
		/**
		* Traverse a nested tree of uploaded file specifications.
		*
		* @param array<string>|array $tmpNameTree
		* @param array<int>|array $sizeTree
		* @param array<int>|array $errorTree
		* @param array<string>|array|null $nameTree
		* @param array<string>|array|null $typeTree
		* @return array<UploadedFile>
		*/
		$recursiveNormalize = static function (
			array $tmpNameTree,
			array $sizeTree,
			array $errorTree,
			?array $nameTree = null,
			?array $typeTree = null
		) use (&$recursiveNormalize): array {
			$normalized = [];
			foreach( $tmpNameTree as $key => $value) {
				if( \is_array($value) ) {
					$normalized[$key] = $recursiveNormalize(
						$tmpNameTree[$key],
						$sizeTree[$key],
						$errorTree[$key],
						$nameTree[$key] ?? null,
						$typeTree[$key] ?? null
					);
					continue;
				}

				$normalized[$key] = self::createFromGlobal([
					"tmp_name" => $tmpNameTree[$key],
					"size" => $sizeTree[$key],
					"error" => $errorTree[$key],
					"name" => $nameTree[$key] ?? null,
					"type" => $typeTree[$key] ?? null,
				]);
			}

			return $normalized;
		};

		/**
		* Normalize an array of file specifications.
		*
		* Loops through all nested files (as determined by receiving an array to the
		* `tmp_name` key of a `$_FILES` specification) and returns a normalized array
		* of UploadedFile instances.
		*
		* This function normalizes a `$_FILES` array representing a nested set of
		* uploaded files as produced by the php-fpm SAPI, CGI SAPI, or mod_php
		* SAPI.
		*
		* @param array $files
		* @return array<UploadedFile>
		*/
		$normalizeUploadedFileSpecification = static function (array $files = []) use (&$recursiveNormalize): array {
			if ( !isset($files["tmp_name"]) || !is_array($files["tmp_name"])
				|| ! isset($files["size"]) || !is_array($files["size"])
				|| ! isset($files["error"]) || !is_array($files["error"]) )
			{
				throw new InvalidArgumentException(sprintf(
					"\$files provided to %s MUST contain each of the keys \"tmp_name\","
					. " \"size\", and \"error\", with each represented as an array;"
					. " one or more were missing or non-array values",
					__FUNCTION__
				));
			}

			return $recursiveNormalize(
				$files["tmp_name"],
				$files["size"],
				$files["error"],
				$files["name"] ?? null,
				$files["type"] ?? null
			);
		};

		$normalized = [];
		foreach( $files as $key => $value ) {
			if( $value instanceof UploadedFileInterface ) {
				$normalized[$key] = $value;
				continue;
			}

			if( \is_array($value) && isset($value["tmp_name"]) && \is_array($value["tmp_name"])) {
				$normalized[$key] = $normalizeUploadedFileSpecification($value);
				continue;
			}

			if( \is_array($value) && isset($value["tmp_name"])) {
				$normalized[$key] = self::createFromGlobal($value);
				continue;
			}

			if( \is_array($value) ) {
				$normalized[$key] = self::createFromGlobals($value);
				continue;
			}

			throw new InvalidArgumentException("Malformed file upload.");
		}

		return $normalized;
	}
}