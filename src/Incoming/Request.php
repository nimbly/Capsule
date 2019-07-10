<?php

namespace Capsule\Incoming;


class Request
{
	/**
	 * HTTP version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * HTTP protocol.
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * HTTP method.
	 *
	 * @var string
	 */
	protected $method;

	/**
	 * All headers sent in Request.
	 *
	 * @var array<string, string>
	 */
	protected $headers = [];

	/**
	 * Host sent in request.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Full path (without query portion) of request.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * All values sent in query.
	 *
	 * @var array<string, string>
	 */
	protected $query = [];

	/**
	 * Raw body of request.
	 *
	 * @var mixed
	 */
	protected $body;

	/**
	 * The body of the request parsed into an array.
	 *
	 * @var array<string, mixed>
	 */
	protected $parsedBody;

	/**
	 * All files sent in request.
	 *
	 * @var array<string, array>
	 */
	protected $files = [];

	/**
	 * The remote address of the connection.
	 *
	 * @var string
	 */
	protected $remoteAddress;

	/**
	 * Create an incoming Request instance from the PHP globals space.
	 *
	 * @return Request
	 */
	public static function createFromGlobals(): Request
	{
		$incomingRequest = new static;

		// Parse the protocol
		if( \preg_match("/(HTTPS?)\/([\d\.]+)/i", $_SERVER['SERVER_PROTOCOL'], $match) ){
			$incomingRequest->protocol = $match[1];
			$incomingRequest->version = $match[2];
		}

		// Get the request method
		$incomingRequest->method = $_SERVER['REQUEST_METHOD'];

		// Get the host name.
		$incomingRequest->host = $_SERVER['HTTP_HOST'];

		// Get the remote address.
		$incomingRequest->remoteAddress = $_SERVER['REMOTE_ADDR'];

		// Get the request path
		$incomingRequest->path = $_SERVER['PATH_INFO'];

		// Get all the headers
		$incomingRequest->headers = \array_change_key_case(\getallheaders());

		// Get the query string portion.
		$incomingRequest->query = $_GET;

		// Parse the request body
		$incomingRequest->body = \file_get_contents("php://input");

		if( empty($incomingRequest->body) ){
			$incomingRequest->parsedBody = $_POST;
		}
		elseif( ($jsonBody = \json_decode($incomingRequest->body, true)) !== null ){
			$incomingRequest->parsedBody = $jsonBody;
		}
		elseif( \parse_str($incomingRequest->body, $formBody) ){
			$incomingRequest->parsedBody = $formBody;
		}

		// Process the uploaded files
		foreach( $_FILES as $name => $file ){
			$incomingRequest->files[$name] = UploadedFile::createFromGlobal($file);
		}

		return $incomingRequest;
	}

	/**
	 * Get the raw string body of the request.
	 *
	 * @return string
	 */
	public function getBody(): string
	{
		return $this->body;
	}

	/**
	 * Check for existance of a request body property.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function has(string $name): bool
	{
		return \array_key_exists($name, $this->parsedBody ?? []);
	}

	/**
	 * Get a request body property.
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $name, $default = null)
	{
		if( $this->has($name) ){
			return $this->parsedBody[$name];
		}

		return $default;
	}

	/**
	 * Get the remote address for this request.
	 *
	 * @return string
	 */
	public function getRemoteAddress(): string
	{
		return $this->remoteAddress;
	}

	/**
	 * Check for existance of header.
	 *
	 * @param string $header
	 * @return boolean
	 */
	public function hasHeader(string $header): bool
	{
		return \array_key_exists(
			\strtolower($header),
			$this->headers
		);
	}

	/**
	 * Get a specific header.
	 *
	 * @param string $header
	 * @param mixed $default
	 * @return string
	 */
	public function getHeader(string $header, $default = null): string
	{
		return $this->hasHeader($header) ? $this->headers[\strtolower($header)] : $default;
	}

	/**
	 * Check for existance of query param.
	 *
	 * @param string $query
	 * @return boolean
	 */
	public function hasQuery(string $query): bool
	{
		return \array_key_exists($query, $this->query);
	}

	/**
	 * Get a specific query param.
	 *
	 * @param string $query
	 * @param mixed $default
	 * @return string
	 */
	public function getQuery(string $query, $default = null): string
	{
		return $this->hasQuery($query) ? $this->query[$query] : $default;
	}

	/**
	 * Check for existance of file.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasFile(string $name): bool
	{
		return \array_key_exists($name, $this->files);
	}

	/**
	 * Get a specific file by its form name.
	 *
	 * @param string $name
	 * @return UploadedFile|null
	 */
	public function getFile(string $name): ?UploadedFile
	{
		if( $this->hasFile($name) ){
			return $this->file[$name];
		}

		return null;
	}
}