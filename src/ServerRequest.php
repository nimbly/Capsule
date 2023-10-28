<?php

namespace Nimbly\Capsule;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * The `ServerRequest` class represents an incoming HTTP request to be handled in your application, service, or
 * middleware. Typically, you would want to create an instance of this class using the `ServerRequestFactory::createFromGlobals()`
 * static method, which populates the ServerRequest from the PHP globals space ($_SERVER, $_POST, $_COOKIES, etc).
 *
 * You might want to create an instance of this class directly when mocking incoming requests for your application
 * to build unit and integration tests.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
	/**
	 * Query parameters sent in request.
	 *
	 * @var array<string,string>
	 */
	protected array $queryParams = [];

	/**
	 * Cookies sent in request.
	 *
	 * @var array<string,string>
	 */
	protected array $cookieParams = [];

	/**
	 * Uploaded files sent in request.
	 *
	 * @var array<array-key,UploadedFileInterface|array<UploadedFileInterface>>
	 */
	protected array $uploadedFiles = [];

	/**
	 * Parsed representation of body contents.
	 *
	 * @var object|array|null
	 */
	protected object|array|null $parsedBody = null;

	/**
	 * Request attributes.
	 *
	 * @var array<string,mixed>
	 */
	protected array $attributes = [];

	/**
	 * Server parameters.
	 *
	 * @var array<string,mixed>
	 */
	protected array $serverParams = [];

	/**
	 * @param string $method The HTTP method of the request. For example, "POST", "GET", etc.
	 * @param string|UriInterface $uri The URI of the resource to be called. For example: "https://api.example.com/books/12345"
	 * @param string|StreamInterface $body The body of the request. If request does not contain a body, you can use a null or empty string value.
	 * @param array<string,mixed> $query An array of key & value pairs for the query params. For example: ["q" => "red socks", "p" => 2]
	 * @param array<string,mixed> $headers An array of key & value pairs for headers in the request. For example: ["Content-Type" => "application/json"]
	 * @param array<string,mixed> $cookies An array of key & value pairs of the cookies in the request. For example: ["c_source" => "web"]
	 * @param array<array-key,UploadedFileInterface|array<UploadedFileInterface>> $files An array of UploadedFileInterface instance for the files to be included in the request.
	 * @param array<string,mixed> $serverParams An array of key & value pairs to be included into the server params space. For example: ["REAL_IP" => "4.4.4.4"]
	 * @param string $version The HTTP protocol version used for this request. Defaults to "1.1".
	 */
	public function __construct(
		string $method,
		string|UriInterface $uri,
		string|StreamInterface|null $body = null,
		array $query = [],
		array $headers = [],
		array $cookies = [],
		array $files  = [],
		array $serverParams = [],
		string $version = "1.1")
	{
		parent::__construct($method, $uri, $body, $headers, $version);

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
	public function withCookieParams(array $cookies): static
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
	public function withQueryParams(array $query): static
	{
		$instance = clone $this;
		$instance->queryParams = $query;

		return $instance;
	}

	/**
	 * @inheritDoc
	 * @return array<array-key,UploadedFileInterface|array<UploadedFileInterface>>
	 */
	public function getUploadedFiles(): array
	{
		return $this->uploadedFiles;
	}

	/**
	 * @inheritDoc
	 * @return static
	 */
	public function withUploadedFiles(array $uploadedFiles): static
	{
		$instance = clone $this;
		$instance->uploadedFiles = $uploadedFiles;

		return $instance;
	}

	/**
	 * @inheritDoc
	 */
	public function getParsedBody(): mixed
	{
		return $this->parsedBody;
	}

	/**
	 * @inheritDoc
	 * @param array|object|null $data
	 * @return static
	 */
	public function withParsedBody($data): static
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
	public function getAttribute($name, $default = null): mixed
	{
		return $this->attributes[$name] ?? $default;
	}

	/**
	 * @inheritDoc
	 * @param string $name
	 * @param mixed $value
	 * @return static
	 */
	public function withAttribute($name, $value): static
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
	public function withoutAttribute($name): static
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
	 * @param string $name Parsed body param name.
	 * @return mixed|null Null is returned if body param does not exist.
	 */
	public function getBodyParam(string $param): mixed
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
	 * @param array<string> $params Array of body param names to return.
	 * @return array<string,mixed>
	 */
	public function onlyBodyParams(array $params): array
	{
		return \array_filter(
			(array) $this->parsedBody,
			function(string $key) use ($params): bool {
				return \in_array($key, $params);
			},
			ARRAY_FILTER_USE_KEY
		);
	}

	/**
	 * Get all request body parameters except those provided.
	 *
	 * @param array<string> $params Array of body param names to exclude.
	 * @return array<string,mixed>
	 */
	public function exceptBodyParams(array $params): array
	{
		return \array_filter(
			(array) $this->parsedBody,
			function(string $key) use ($params): bool {
				return !\in_array($key, $params);
			},
			ARRAY_FILTER_USE_KEY
		);
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
	 * @return UploadedFileInterface|array<UploadedFileInterface>|null
	 */
	public function getUploadedFile(string $name): UploadedFileInterface|array|null
	{
		return $this->getUploadedFiles()[$name] ?? null;
	}
}