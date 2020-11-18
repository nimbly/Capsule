<?php declare(strict_types=1);

namespace Capsule\Factory;

use Capsule\Factory\UploadedFileFactory;
use Capsule\ServerRequest;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
	/**
	 * @inheritDoc
	 */
	public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
	{
		return new ServerRequest($method, $uri, null, [], [], [], [], $serverParams);
	}

	/**
	 * Create a Capsule ServerRequest instance from another PSR-7 ServerRequestInterface instance.
	 *
	 * @param ServerRequestInterface $serverRequest
	 * @return ServerRequest
	 */
	public static function createServerRequestFromPsr7(ServerRequestInterface $psr7ServerRequest): ServerRequest
	{
		return new ServerRequest(
			$psr7ServerRequest->getMethod(),
			$psr7ServerRequest->getUri(),
			$psr7ServerRequest->getBody(),
			$psr7ServerRequest->getQueryParams(),
			$psr7ServerRequest->getHeaders(),
			$psr7ServerRequest->getCookieParams(),
			$psr7ServerRequest->getUploadedFiles(),
			$psr7ServerRequest->getServerParams(),
			$psr7ServerRequest->getProtocolVersion()
		);
	}

	/**
	 * Create a ServerRequest instance from the PHP globals space.
	 *
	 * Uses values from:
	 * 	$_SERVER
	 * 	$_FILES
	 * 	$_COOKIE
	 * 	$_POST
	 * 	$_GET
	 *
	 * @return ServerRequest
	 */
	public static function createFromGlobals(): ServerRequest
	{
		// Build out the URI
		$scheme = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https" : "http";

		$uri = $scheme . "://" .
		($_SERVER['HTTP_HOST'] ?? (($_SERVER['SERVER_NAME'] ?? "") . ":" . ($_SERVER['SERVER_PORT'] ?? ""))) .
		($_SERVER['REQUEST_URI'] ?? "/");

		// Capture the version.
		\preg_match("/^HTTP\/([\d\.]+)$/i", $_SERVER['SERVER_PROTOCOL'] ?? "", $versionMatch);

		// Get the request body first by getting raw input from php://input.
		$body = \file_get_contents("php://input");

		// Process the uploaded files into an array<UploadedFile>.
		$files = [];
		foreach( $_FILES as $name => $file ){
			$files[$name] = UploadedFileFactory::createFromGlobal($file);
		}

		return new ServerRequest(
			$_SERVER['REQUEST_METHOD'],
			$uri,
			!empty($body) ? $body : $_POST,
			$_GET,
			\array_change_key_case(\getallheaders()),
			$_COOKIE,
			$files,
			$_SERVER,
			$versionMatch[2] ?? "1.1"
		);
	}
}