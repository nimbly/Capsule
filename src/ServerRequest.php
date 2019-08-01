<?php

namespace Capsule;

use Capsule\Stream\FileStream;
use Psr\Http\Message\ServerRequestInterface;


class ServerRequest extends Request implements ServerRequestInterface
{
	/**
	 * Remote address of client.
	 *
	 * @var string
	 */
	protected $remoteAddress;

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
	 * @var array<string, string>
	 */
	protected $parsedBody = [];

	/**
	 * Request attributes.
	 *
	 * @var array<string, mixed>
	 */
	protected $attributes = [];

	/**
	 * Create an incoming Request instance from the PHP globals space.
	 *
	 * @return ServerRequest
	 */
	public static function createFromGlobals(): ServerRequest
	{
		$serverRequest = new static;

		// Parse the protocol and version
		if( \preg_match("/(HTTPS?)\/([\d\.]+)/i", $_SERVER['SERVER_PROTOCOL'], $match) ){

			$serverRequest->uri = new Uri(
				\strtolower($match[1]) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
			);

			$serverRequest->version = $match[2];
		}

		// Get the request method
		$serverRequest->method = $_SERVER['REQUEST_METHOD'];

		// Get the remote address.
		$serverRequest->remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';

		// Get all the headers
		foreach( \array_change_key_case(\getallheaders()) as $header => $value ){
			$serverRequest = $serverRequest->withAddedHeader($header, $value);
		}

		// Get the query params.
		\parse_str($serverRequest->getUri()->getQuery(), $serverRequest->queryParams);

		// Parse the request body
		$body = \file_get_contents("php://input");

		if( empty($body) ){
			$serverRequest->parsedBody = $_POST;
		}
		elseif( ($jsonBody = \json_decode($body, true)) !== null ){
			$serverRequest->parsedBody = $jsonBody;
		}
		elseif( \parse_str($body, $formBody) ){
			$serverRequest->parsedBody = $formBody;
		}

		// Process the uploaded files
		foreach( $_FILES as $name => $file ){
			$serverRequest->uploadedFiles[$name] = UploadedFile::createFromGlobal($file);
		}

		// Process the cookies
		$serverRequest->cookieParams = $_COOKIE;

		return $serverRequest;
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
		$instance->uri = $this->uri->withQuery($query);

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