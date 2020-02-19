# Capsule

[![Latest Stable Version](https://img.shields.io/packagist/v/nimbly/capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/capsule)
[![Build Status](https://img.shields.io/travis/nimbly/capsule.svg?style=flat-square)](https://travis-ci.org/nimbly/capsule)
[![Code Coverage](https://img.shields.io/coveralls/github/nimbly/Capsule.svg?style=flat-square)](https://coveralls.io/github/nimbly/Capsule)
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

The `ServerRequest` object represents an *incoming* HTTP request into your application, to be used with a PSR-7 compliant HTTP framework.

```php
$serverRequest = new ServerRequest("get", "https://example.org/books");

$response = $application->dispatch($serverRequest);
```

#### Creating from globals

Typically, you will want to create a `ServerRequest` instance from the PHP globals space (`$_SERVER`, `$_POST`, `$_GET`, `$_FILES`, and `$_COOKIES`) for your incoming requests. The `ServerRequestFactory` provides a static method to create such an instance.

```php
$serverRequest = ServerRequestFactory::createFromGlobals();

$response = $application->dispatch($serverRequest);
```
#### Helpers

The `ServerRequest` instance offers helpers to test for and access various request property parameters.

#### Parsed body helpers

```php
if( $serverRequest->hasParsedBodyParam('foo') ){
	// Do the foo...
}

/**
 * Get a single param ("bar") from the parsed body.
 */
$bar = $serverRequest->getParsedBodyParam('bar');

/**
 * Get *only* the provided params from the parsed body.
 */
$serverRequest->onlyBodyParams(['foo', 'bar']);

/**
 * Get all params from the parsed body *except* those provided.
 */
$serverRequest->exceptParsedBodyParams(['foo', 'bar']);
```

#### Query param helpers

```php
if( $serverRequest->hasQueryParam('foo') ){
	// Do the foo...
}

$foo = $serverRequest->getQueryParam('foo');
```

#### Uploaded file helpers

```php
if( $serverRequest->hasUploadedFile('avatar') ){
	// Do something
}

$avatar = $serverRequest->getUploadedFile('avatar');
```

### Response

The `Response` object represents an HTTP response to either a `Request` or a `ServerRequest` action.

```php
$response = new Response(200, \json_encode(["foo" => "bar"]), ['Content-Type' => 'application/json']);
```

## HTTP Factory (PSR-17)

Capsule includes a set of PSR-17 factory classes to be used to create `Request`, `ServerRequest`,  `Response`, `Stream`, `UploadedFile`, and `Uri` instances.