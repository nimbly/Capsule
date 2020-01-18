<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\BufferStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
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
	 * @param string|UriInterface $uri
	 * @param string|array|object|null $body
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
		parent::__construct($method, $uri, \is_string($body) ? new BufferStream($body) : null, $headers, $version);

		// Allow assigning query params to the server request via the URI.
		\parse_str($this->getUri()->getQuery(), $queryParams);

		$this->queryParams = \array_merge($query, $queryParams ?: []);
		$this->uploadedFiles = $files;
		$this->cookieParams = $cookies;
		$this->serverParams = $serverParams;
		$this->parsedBody = $this->parseRequestBody($body);
	}

	/**
	 * Attempt to parse the request body.
	 *
	 * @param mixed $body
	 * @return mixed
	 */
	private function parseRequestBody($body)
	{
		// Body has already been parsed.
		if( \is_array($body) || \is_object($body) ){
			return $body;
		}

		// String content body - let's try and parse it.
		if( \is_string($body) && $this->hasHeader('Content-Type') ) {

			$contentType = \strtolower($this->getHeaderLine('Content-Type'));

			if( \in_array($contentType, ['application/x-www-form-urlencoded', 'multipart/form-data']) ){

				if( $this->getMethod() === "POST" && !empty($_POST) ){
					return $_POST;
				}

				\parse_str($body, $parsedBody);
				return $parsedBody;
			}

			elseif( \stristr($contentType, 'application/json') !== false ){
				return \json_decode($body, true);
			}
		}

		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function getServerParams(): array
	{
		return $this->serverParams;
	}

	/**
	 * @inheritDoc
	 */
	public function getCookieParams(): array
	{
		return $this->cookieParams;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withCookieParams(array $cookies): ServerRequest
	{
		$instance = clone $this;
		$instance->cookieParams = $cookies;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getQueryParams(): array
	{
		return $this->queryParams;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withQueryParams(array $query): ServerRequest
	{
		$instance = clone $this;
		$instance->queryParams = $query;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getUploadedFiles(): array
	{
		return $this->uploadedFiles;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withUploadedFiles(array $uploadedFiles): ServerRequest
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
	 * @return static
	 */
	public function withParsedBody($data): ServerRequest
	{
		$instance = clone $this;
		$instance->parsedBody = $data;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getAttributes(): array
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
	 * @return static
	 */
	public function withAttribute($name, $value): ServerRequest
	{
		$instance = clone $this;
		$instance->attributes[$name] = $value;

		return $instance;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withoutAttribute($name): ServerRequest
	{
		$instance = clone $this;
		unset($instance->attributes[$name]);

		return $instance;
	}

	/**
	 * Check for the presence of a parameter in the parsed request body.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasRequestParam(string $param): bool
	{
		return \array_key_exists($param, (array) ($this->getParsedBody() ?? []));
	}

	/**
	 * Get a request parameter from the parsed request body.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function getRequestParam(string $param)
	{
		if( \is_object($this->parsedBody) &&
			\property_exists($this->parsedBody, $param) ){
			return $this->parsedBody->{$param};
		}

		if( \is_array($this->parsedBody) &&
			\array_key_exists($param, $this->parsedBody) ){
			return $this->parsedBody[$param];
		}

		return null;
	}

	/**
	 * Get only the request body parameters provided.
	 *
	 * @param array<string> $params
	 * @return array<string, mixed>
	 */
	public function onlyRequestParams(array $params): array
	{
		$only = [];

		foreach( (array) $this->parsedBody as $key => $value ){

			if( \in_array($key, $params) ){
				$only[$key] = $value;
			}
		}

		return $only;
	}

	/**
	 * Get all request body parameters except those provided.
	 *
	 * @param array<string> $params
	 * @return array<string, mixed>
	 */
	public function exceptRequestParams(array $params): array
	{
		$except = [];

		foreach( (array) $this->parsedBody as $key => $value ){
			if( !\in_array($key, $params) ){
				$except[$key] = $value;
			}
		}

		return $except;
	}

	/**
	 * Check for the presence of a query parameter.
	 *
	 * @param string $param
	 * @return boolean
	 */
	public function hasQueryParam(string $param): bool
	{
		return \array_key_exists($param, $this->queryParams);
	}

	/**
	 * Get a query parameter from the query params.
	 *
	 * @param string $param
	 * @return string|null
	 */
	public function getQueryParam(string $param): ?string
	{
		return $this->queryParams[$param] ?? null;
	}

	/**
	 * Get all request values from *both* the parsed request body and the query params.
	 *
	 * @return array<string, mixed>
	 */
	public function getAllParams(): array
	{
		return \array_merge(
			(array) ($this->parsedBody ?? []),
			$this->queryParams
		);
	}

	/**
	 * Get an UploadedFileInterface instance by its name.
	 *
	 * @param string $name
	 * @return UploadedFileInterface|null
	 */
	public function getUploadedFile(string $name): ?UploadedFileInterface
	{
		return $this->getUploadedFiles()[$name] ?? null;
	}
}