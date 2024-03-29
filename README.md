# Capsule

[![Latest Stable Version](https://img.shields.io/packagist/v/nimbly/capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/capsule)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/nimbly/capsule/php.yml?style=flat-square)](https://github.com/nimbly/Capsule/actions/workflows/php.yml)
[![Codecov branch](https://img.shields.io/codecov/c/github/nimbly/capsule/master?style=flat-square)](https://app.codecov.io/github/nimbly/Capsule)
[![License](https://img.shields.io/github/license/nimbly/capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/capsule)

Capsule is a simple [PSR-7](https://www.php-fig.org/psr/psr-7/) HTTP message interface and [PSR-17](https://www.php-fig.org/psr/psr-17) HTTP factory implementation.

## Install
```bash
composer require nimbly/capsule
```

## HTTP Message (PSR-7)

### Request

The `Request` object represents an *outbound* HTTP request your application would like to make, typically to be used with a PSR-18 compliant HTTP client.

```php
$request = new Request("get", "https://example.org/books");

$response = $httpClient->sendRequest($request);
```

### ServerRequest

The `ServerRequest` object represents an *incoming* HTTP request into your application, to be used with a PSR-7 compliant HTTP framework or other library.

```php
$serverRequest = new ServerRequest("get", "https://example.org/books");

$response = $framework->dispatch($serverRequest);
```

#### Creating from globals

Typically, you will want to create a `ServerRequest` instance from the PHP globals space (`$_SERVER`, `$_POST`, `$_GET`, `$_FILES`, and `$_COOKIES`) for your incoming requests. The `ServerRequestFactory` provides a static method to create such an instance.

```php
$serverRequest = ServerRequestFactory::createFromGlobals();

$response = $framework->dispatch($serverRequest);
```
#### Helpers

The `ServerRequest` instance offers helpers to test for and access various request property parameters.

#### Parsed body helpers

```php
if( $serverRequest->hasBodyParam("foo") ){
	// Do the foo...
}

/**
 * Get a single param ("bar") from the parsed body.
 */
$bar = $serverRequest->getBodyParam("bar");

/**
 * Get *only* the provided params from the parsed body.
 */
$serverRequest->onlyBodyParams(["foo", "bar"]);

/**
 * Get all params from the parsed body *except* those provided.
 */
$serverRequest->exceptBodyParams(["foo", "bar"]);
```

#### Query param helpers

```php
if( $serverRequest->hasQueryParam("foo") ){
	// Do the foo...
}

$foo = $serverRequest->getQueryParam("foo");
```

#### Uploaded file helpers

```php
if( $serverRequest->hasUploadedFile("avatar") ){
	// Do something
}

$avatar = $serverRequest->getUploadedFile("avatar");
```

### Response

The `Response` object represents an HTTP response to either a `Request` or a `ServerRequest` action.

```php
$response = new Response(200, \json_encode(["foo" => "bar"]), ["Content-Type" => "application/json"]);
```

### Response Status

Capsule provides a `ResponseStatus` helper class with HTTP response codes as constants and reason phrases.

```php
$response = new Response(ResponseStatus::NOT_FOUND);
```

```php
$phrase = ResponseStatus::getPhrase(ResonseStatus::NOT_FOUND);

echo $phrase; // Outputs "Not Found"
```

## HTTP Factory (PSR-17)

Capsule includes a set of PSR-17 factory classes to be used to create `Request`, `ServerRequest`,  `Response`, `Stream`, `UploadedFile`, and `Uri` instances.

`RequestFactory`, `ServerRequestFactory`, `ResponseFactory`, `StreamFactory`, `UploadedFileFactory`, and `UriFactory`.