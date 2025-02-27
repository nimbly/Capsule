# Capsule

[![Latest Stable Version](https://img.shields.io/packagist/v/nimbly/capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/capsule)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/nimbly/capsule/php.yml?style=flat-square)](https://github.com/nimbly/Capsule/actions/workflows/coverage.yml)
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

Capsule provides a `ResponseStatus` enum with HTTP response codes and reason phrases.

```php
$response = new Response(ResponseStatus::NOT_FOUND));
```

```php
$phrase = ResponseStatus::NOT_FOUND->getPhrase();

echo $phrase; // Outputs "Not Found"
```

## HTTP Factory (PSR-17)

Capsule includes a set of PSR-17 factory classes to be used to create `Request`, `ServerRequest`,  `Response`, `Stream`, `UploadedFile`, and `Uri` instances, found in the `Nimbly\Capsule\Factory` namespace. These factories are typically used with other libraries that are PSR-7 agnostic. They're also useful for creating mocked instances in unit testing.

### RequestFactory
```php
$requestFactory = new RequestFactory;
$request = $requestFactory->createRequest("get", "https://api.example.com");
```

### ServerRequestFactory
```php
$serverRequestFactory = new ServerRequestFactory;
$serverRequest = $serverRequestFactory->createServerRequest("post", "https://api.example.com/books");
```

In addition, the `ServerRequestFactory` provides several static methods for creating server requests.

#### Creating ServerRequest from PHP globals
You can create a `ServerRequest` instance from the PHP globals space ($_POST, $_GET, $_FILES, $_SERVER, and $_COOKIES).

```php
$serverRequest = ServerRequestFactory::createFromGlobals();
```

#### Creating ServerRequest from another PSR-7 ServerRequest
You can create a Capsule `ServerRequest` instance from another PSR-7 ServerRequest instance:

```php
$serverRequest = ServerRequestFactory::createServerRequestFromPsr7($otherServerRequest);
```

### ResponseFactory

```php
$responseFactory = new ResponseFactory;
$response = $responseFactory->createResponse(404);
```

### StreamFactory

#### Create a stream from string content

```php
$streamFactory = new StreamFactory;
$stream = $streamFactory->createStream(\json_encode($body));
```

#### Create a stream from a file

```php
$streamFactory = new StreamFactory;
$stream = $streamFactory->createStreamFromFile("/reports/q1.pdf");
```

#### Create a stream from any resource

```php
$resource = \fopen("https://example.com/reports/q1.pdf", "r");

$streamFactory = new StreamFactory;
$stream = $streamFactory->createStreamFromResource($resource);
```

Alternatively, these methods are also available statically:

```php
// Create a stream from a string.
$stream = StreamFactory::createFromString(\json_encode($body));

// Create a stream from a local file.
$stream = StreamFactory::createFromFile("/reports/q1.pdf");

// Create a stream from a PHP resource.
$resource = \fopen("https://example.com/reports/q1.pdf", "r");
$stream = StreamFactory::createFromResource($resource);
```

### UploadedFileFactory

#### Create an UploadedFile instance
```php
$uploadedFileFactory = new UploadedFileFactory;

$stream = StreamFactory::createFromFile("/tmp/upload");

$uploadedFile = $uploadedFileFactory->createUploadedFile(
    $stream,
    $stream->getSize(),
    UPLOAD_ERR_OK,
    "q1_report.pdf",
    "application/pdf"
);
```

### UriFactory

The `UriFactory` allows you to create and parse URIs.

```php
$uriFactory = new UriFactory;
$uri = $uriFactory->createUri("https://api.example.com/v1/books?a=Kurt+Vonnegut");
```

This method is also available statically:

```php
$uri = UriFactory::createFromString("https://api.example.com/v1/books?a=Kurt+Vonnegut");