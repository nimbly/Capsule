<?php declare(strict_types=1);

namespace Capsule;

use Capsule\Stream\BufferStream;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;


class ServerRequest extends Request implements ServerRequestInterface
{
	/**
	 * Query parameters sent in request.
	 *
	 * @var array<string,string>
	 */
	protected $queryParams = [];

	/**
	 * Cookies sent in request.
	 *
	 * @var array<string,string>
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
	 * @var array<string,mixed>
	 */
	protected $attributes = [];

	/**
	 * Server parameters.
	 *
	 * @var array<string,mixed>
	 */
	protected $serverParams = [];

	/**
	 * ServerRequest constructor.
	 *
	 * @param string $method
	 * @param string|UriInterface $uri
	 * @param string|array|object|null $body
	 * @param array<string,mixed> $query
	 * @param array<string,mixed> $headers
	 * @param array<string,mixed> $cookies
	 * @param array<UploadedFile> $files
	 * @param array<string,mixed> $serverParams
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

		if( !$body instanceof StreamInterface ){
			if( \is_string($body) ){
				$body = new BufferStream($body);
			}
			elseif( \is_array($body) || \is_object($body) ){
				$this->parsedBody = $body;
			}
		}

		parent::__construct($method, $uri, $body instanceof StreamInterface ? $body : null, $headers, $version);

		// Allow assigning query params to the server request via the URI.
		\parse_str($this->getUri()->getQuery(), $queryParams);

		$this->queryParams = \array_merge($queryParams ?: [], $query);
		$this->uploadedFiles = $files;
		$this->cookieParams = $cookies;
		$this->serverParams = $serverParams;
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
	 * @param array|object|null $data
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
	 * @param string $name
	 * @param mixed $value
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
	 * @param string $name
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
	 * Note, this method will return *true* as long as the param exists, even
	 * if the value of the param is null or generally "falsey".
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasBodyParam(string $param): bool
	{
		return \array_key_exists($param, (array) ($this->getParsedBody() ?? []));
	}

	/**
	 * Get a request parameter from the parsed request body.
	 *
	 * @param string $name
	 * @return mixed|null
	 */
	public function getBodyParam(string $param)
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
	 * @return array<string,mixed>
	 */
	public function onlyBodyParams(array $params): array
	{
		return \array_filter((array) $this->parsedBody, function(string $key) use ($params): bool {

			return \in_array($key, $params);

		}, ARRAY_FILTER_USE_KEY);
	}

	/**
	 * Get all request body parameters except those provided.
	 *
	 * @param array<string> $params
	 * @return array<string,mixed>
	 */
	public function exceptBodyParams(array $params): array
	{
		return \array_filter((array) $this->parsedBody, function(string $key) use ($params): bool {

			return !\in_array($key, $params);

		}, ARRAY_FILTER_USE_KEY);
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
	 * @return array<string,mixed>
	 */
	public function getAllParams(): array
	{
		return \array_merge(
			(array) ($this->parsedBody ?? []),
			$this->queryParams
		);
	}

	/**
	 * Check for the presense of an uploaded file.
	 *
	 * @param string $name
	 * @return boolean
	 */
	public function hasUploadedFile(string $name): bool
	{
		return \array_key_exists($name, $this->getUploadedFiles());
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