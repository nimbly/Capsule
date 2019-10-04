# Capsule

[![Latest Stable Version](https://img.shields.io/packagist/v/nimbly/Capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/Capsule)
[![Build Status](https://img.shields.io/travis/nimbly/Capsule.svg?style=flat-square)](https://travis-ci.org/nimbly/Capsule)
[![Code Coverage](https://img.shields.io/coveralls/github/nimbly/Capsule.svg?style=flat-square)](https://coveralls.io/github/nimbly/Capsule)
[![License](https://img.shields.io/github/license/nimbly/Capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/Capsule)

Capsule is a simple [PSR-7](https://www.php-fig.org/psr/psr-7/) HTTP message interface and [PSR-17](https://www.php-fig.org/psr/psr-17) HTTP factory implementation.

**Note:** This library is simply an HTTP Request, ServerRequest, and Response message interface implementation. If you are looking for an HTTP client, checkout [nimbly/Shuttle](https://github.com/nimbly/shuttle). Shuttle is a [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client utilizing Capsule as its HTTP message implementation.

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

The ```ServerRequest``` object represents an *incoming* request into your application, typically to be used with a PSR-7 compliant framework.

You can create a new ServerRequest instance to have full control over the object.

```php
$serverRequest = new ServerRequest("get", "https://example.org/books", '{"foo": "bar"}', ["p" => 1], ["Content-Type" => "application/json"]);

$response = $application->dispatch($serverRequest);
```

Better yet &ndash; use the ```createFromGlobals()``` static method to have an instance created for you automatically using PHP's global variables ($_SERVER, $_POST, $_GET, $_FILES, $_COOKIES).

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

Factory class to be used to create ```Request```, ```ServerRequest```, and ```ServerResponse``` instances.

```php
$factory = new Factory;

// Create a Request instance
$request = $factory->createRequest("get", "http://example.org/books");

// Create a ServerRequest instance
$serverRequest = $factory->createServerRequest("get", "http://example.org/books", \array_merge($_SERVER, ['CustomParam1' => 'Custom Value']));

// Create a Response instance
$response = $factory->createResponse(200);
```