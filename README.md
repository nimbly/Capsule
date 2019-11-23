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

The ```Request``` object represents and *outbound* request your application would like to make, to be used with a PSR-18 compliant HTTP client.

```php
$request = new Request("get", "https://example.org/books", \json_encode(["foo" => "bar"]), ["Content-Type" => "application/json"]);

$response = $httpClient->sendRequest($request);
```

### ServerRequest

The ```ServerRequest``` object represents an *incoming* request into your application, to be used with a PSR-7 compliant HTTP framework.

```php
$serverRequest = new ServerRequest("get", "https://example.org/books", '{"foo": "bar"}', ["p" => 1], ["Content-Type" => "application/json"]);

$response = $application->dispatch($serverRequest);
```

#### Creating from globals

Typically, you will want to create a `ServerRequest` instance from the PHP globals space (`$_SERVER`, `$_POST`, `$_GET`, `$_FILES`, and `$_COOKIES`) for your incoming requests. Use the `createFromGlobals()` static method to have an instance created for you automatically.

```php
$serverRequest = ServerRequest::createFromGlobals();

$response = $application->dispatch($serverRequest);
```

### Response

The ```Response``` object represents the response to either a ```Request``` or a ```ServerRequest``` action.

```php
$response = new Response(200, \json_encode(["foo" => "bar"]), ['Content-Type' => 'application/json']);
```

## HTTP Factory (PSR-17)

Capsule includes a PSR-17 factory class to be used to create ```Request```, ```ServerRequest```, and ```ServerResponse``` instances.

```php
$factory = new Factory;

// Create a Request instance
$request = $factory->createRequest("get", "http://example.org/books");

// Create a ServerRequest instance
$serverRequest = $factory->createServerRequest("get", "http://example.org/books", \array_merge($_SERVER, ['CustomParam1' => 'Custom Value']));

// Create a Response instance
$response = $factory->createResponse(200);
```