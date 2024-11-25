<?php

namespace Nimbly\Capsule\Factory;

use Nimbly\Capsule\UploadedFile;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

/**
 * With this factory you can generate an UploadedFile instance.
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createUploadedFile(
		StreamInterface $stream,
		?int $size = null,
		int $error = UPLOAD_ERR_OK,
		?string $clientFilename = null,
		?string $clientMediaType = null
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
	 * Create a tree of UploadedFile instances.
	 *
	 * @param array<array-key,array{tmp_name:string,name:string,type:string,size:int,error:int}|array{tmp_name:array<string>,name:array<string>,type:array<string>,size:array<int>,error:array<int>}> $files Tree of uploaded files in the PHP $_FILES format.
	 * @return array<array-key,UploadedFile|array<UploadedFile>>
	 */
	public static function createFromGlobals(array $files): array
	{
		$uploaded_files = [];

		foreach( $files as $name => $file ){
			if( \is_array($file["tmp_name"]) ) {
				for( $i = 0; $i < \count($file["tmp_name"]); $i++ ){
					/**
					 * @psalm-suppress PossiblyInvalidArrayAccess
					 * @psalm-suppress UndefinedMethod
					 */
					$uploaded_files[$name][] = self::createFromGlobal([
						"tmp_name" => $file["tmp_name"][$i],
						"name" => $file["name"][$i],
						"type" => $file["type"][$i],
						"size" => $file["size"][$i],
						"error" => $file["error"][$i]
					]);
				}
			}
			else {
				/**
				 * @psalm-suppress ArgumentTypeCoercion
				 */
				$uploaded_files[$name] = self::createFromGlobal($file);
			}
		}

		return $uploaded_files;
	}
}