<?php

namespace Capsule;

use Capsule\Stream\BufferStream;
use Psr\Http\Message\ServerRequestInterface;


class ServerRequest extends Request implements ServerRequestInterface
{
	/**
	 * Query parameters sent in request.
	 *
	 * @var array<string, string>
	 */
	protected $queryParams = [];

	/**
	 * Cookies sent in request.
	 *
	 * @var array<string, string>
	 */
	protected $cookieParams = [];

	/**
	 * Uploaded files sent in request.
	 *
	 * @var array<UploadedFile>
	 */
	protected $uploadedFiles = [];

	/**
	 * Parsed representation of body contents.
	 *
	 * @var null|array|object
	 */
	protected $parsedBody;

	/**
	 * Request attributes.
	 *
	 * @var array<string, mixed>
	 */
	protected $attributes = [];

	/**
	 * Create a ServerRequest instance.
	 *
	 * @param string $method
	 * @param Uri|string $uri
	 * @param object|array|string|null $body
	 * @param array<string, mixed> $query
	 * @param array<string, mixed> $headers
	 * @param array<string, mixed> $cookies
	 * @param array<UploadedFile> $files
	 * @param string $version
	 * @return ServerRequest
	 */
	public static function create(
		string $method,
		$uri,
		$body,
		array $query,
		array $headers,
		array $cookies,
		array $files,
		string $version = "1.1"): ServerRequest
	{
		$serverRequest = new static;
		$serverRequest->method = $method;

		if( $uri instanceof Uri ){
			$serverRequest->uri = $uri;
		}
		else {
			$serverRequest->uri = Uri::createFromString($uri);
		}

		foreach( $headers as $header => $value ){
			$serverRequest = $serverRequest->withAddedHeader($header, $value);
		}

		$serverRequest->queryParams = $query;

		if( \is_array($body) ){
			$serverRequest->body = new BufferStream(\http_build_query($body));
			$serverRequest->parsedBody = $body;
		}
		elseif( \is_string($body) ) {
			$serverRequest->parsedBody = $serverRequest->parseStringBody($body);
			$serverRequest->body = new BufferStream($body);
		}
		elseif( \is_object($body) ){
			$serverRequest->body = new BufferStream(\json_encode((array) $body));
			$serverRequest->parsedBody = (array) $body;
		}

		$serverRequest->uploadedFiles = $files;
		$serverRequest->cookieParams = $cookies;
		$serverRequest->version = $version;

		return $serverRequest;
	}

	/**
	 * Parse the body.
	 *
	 * @param string $body
	 * @return null|array|object
	 */
	private function parseStringBody(string $body)
	{
		// Use the Content-Type header to inform the parsing.
		if( ($contentType = $this->getHeader('Content-Type')) ){

			if( \stripos($contentType[0], 'application/json') !== false ){
				return (array) \json_decode($body);
			}
			elseif( \stripos($contentType[0], 'application/x-www-form-urlencoded') !== false ||
					\stripos($contentType[0], 'multipart/form-data') !== false ){
				\parse_str($body, $parsedBody);
				return $parsedBody;
			}
		}

		return null;
	}

	/**
	 * Create an incoming Request instance from the PHP globals space.
	 *
	 * @return ServerRequest
	 */
	public static function createFromGlobals(): ServerRequest
	{
		if( \preg_match("/(HTTPS?)\/([\d\.]+)/i", $_SERVER['SERVER_PROTOCOL'] ?? "", $match) == false ){
			throw new \Exception('Cannot parse request.');
		}

		// Build out the URI
		$uri = \strtolower($match[1]) . '://' .
		($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'])) .
		$_SERVER['REQUEST_URI'] ?? "/";

		// Capture the version.
		$version = $match[2];

		// Get the request body first by getting raw input from php://input.
		$body = \file_get_contents("php://input");

		// Process the uploaded files into an array of UploadedFile.
		$files = [];
		foreach( $_FILES as $name => $file ){
			$files[$name] = UploadedFile::createFromGlobal($file);
		}

		return self::create(
			$_SERVER['REQUEST_METHOD'],
			$uri,
			!empty($body) ? $body : $_POST,
			$_GET,
			\array_change_key_case(\getallheaders()),
			$_COOKIE,
			$files ?? [],
			$version ?? "1.1"
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getServerParams()
	{
		return $_SERVER;
	}

	/**
	 * @inheritDoc
	 */
	public function getCookieParams()
	{
		return $this->cookieParams;
	}

	/**
	 * @inheritDoc
	 */
	public function withCookieParams(array $cookies)
	{
		$instance = clone $this;
		$instance->cookieParams = $cookies;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryParams()
	{
		return $this->queryParams;
	}

	/**
	 * @inheritDoc
	 */
	public function withQueryParams(array $query)
	{
		$instance = clone $this;
		$instance->queryParams = $query;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getUploadedFiles()
	{
		return $this->uploadedFiles;
	}

	/**
	 * @inheritDoc
	 */
	public function withUploadedFiles(array $uploadedFiles)
	{
		$instance = clone $this;
		$instance->uploadedFiles = $uploadedFiles;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getParsedBody()
	{
		return $this->parsedBody;
	}

	/**
	 * @inheritDoc
	 */
	public function withParsedBody($data)
	{
		$instance = clone $this;
		$instance->parsedBody = (array) $data;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getAttributes()
	{
		return $this->attributes;
	}

	/**
	 * @inheritDoc
	 */
	public function getAttribute($name, $default = null)
	{
		return $this->attributes[$name] ?? $default;
	}

	/**
	 * @inheritDoc
	 */
	public function withAttribute($name, $value)
	{
		$instance = clone $this;
		$instance->attributes[$name] = $value;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function withoutAttribute($name)
	{
		$instance = clone $this;
		unset($instance->attributes[$name]);

		return $instance;
	}
}