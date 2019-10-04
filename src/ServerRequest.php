<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\BufferStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;


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
	 * Server parameters.
	 *
	 * @var array<string, mixed>
	 */
	protected $serverParams = [];

	/**
	 * ServerRequest constructor.
	 *
	 * @param string $method
	 * @param UriInterface|string $uri
	 * @param object|array|string|null $body
	 * @param array<string, mixed> $query
	 * @param array<string, mixed> $headers
	 * @param array<string, mixed> $cookies
	 * @param array<UploadedFile> $files
	 * @param array<string, mixed> $serverParams
	 * @param string $version
	 */
	public function __construct(
		string $method,
		$uri,
		$body = null,
		array $query = [],
		array $headers = [],
		array $cookies = [],
		array $files  = [],
		array $serverParams = [],
		string $version = "1.1")
	{
		parent::__construct($method, $uri, $this->createStreamFromBody($body), $headers, $version);

		$this->parsedBody = $this->parseBody($body);
		$this->queryParams = $query;
		$this->uploadedFiles = $files;
		$this->cookieParams = $cookies;
		$this->serverParams = $serverParams;
	}

	/**
	 * Create a StreamInterface instance from the Request body.
	 *
	 * @param mixed $body
	 * @return StreamInterface
	 */
	private function createStreamFromBody($body): StreamInterface
	{
		if( \is_array($body) ){
			$stream = new BufferStream(\http_build_query($body));
		}
		elseif( \is_string($body) ) {
			$stream = new BufferStream($body);
		}
		elseif( \is_object($body) ){
			$stream = new BufferStream(\json_encode((array) $body));
		}
		else {
			$stream = new BufferStream;
		}

		return $stream;
	}

	/**
	 * Parse the body of the ServerRequest into something useable (array or \stdClass).
	 *
	 * @param mixed $body
	 * @return null|array|object
	 */
	private function parseBody($body)
	{
		if( \is_string($body) ){
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
		}

		elseif( \is_array($body) ||
				\is_object($body)) {
			return (array) $body;
		}

		return null;
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
		($_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] . ":" . $_SERVER['SERVER_PORT'])) .
		$_SERVER['REQUEST_URI'] ?? "/";

		// Capture the version.
		\preg_match("/^HTTP\/([\d\.]+)$/i", $_SERVER['SERVER_PROTOCOL'] ?? "", $versionMatch);

		// Get the request body first by getting raw input from php://input.
		$body = \file_get_contents("php://input");

		// Process the uploaded files into an array<UploadedFile>.
		$files = [];
		foreach( $_FILES as $name => $file ){
			$files[$name] = UploadedFile::createFromGlobal($file);
		}

		return new static(
			$_SERVER['REQUEST_METHOD'],
			$uri,
			!empty($body) ? $body : $_POST,
			$_GET,
			\array_change_key_case(\getallheaders()),
			$_COOKIE,
			$files ?? [],
			$_SERVER,
			$versionMatch[2] ?? "1.1"
		);
	}

	/**
	 * @inheritDoc
	 */
	public function getServerParams()
	{
		return $this->serverParams;
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