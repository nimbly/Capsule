# Capsule

[![Latest Stable Version](https://img.shields.io/packagist/v/nimbly/Capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/Capsule)
[![Build Status](https://img.shields.io/travis/nimbly/Capsule.svg?style=flat-square)](https://travis-ci.org/nimbly/Capsule)
[![Code Coverage](https://img.shields.io/coveralls/github/nimbly/Capsule.svg?style=flat-square)](https://coveralls.io/github/nimbly/Capsule)
[![License](https://img.shields.io/github/license/nimbly/Capsule.svg?style=flat-square)](https://packagist.org/packages/nimbly/Capsule)

Simple PSR-7 HTTP message implementation -- no bells, no whistles.

## Using globals
You can create a ```Request``` instance from PHP's ```$_SERVER``` super global by calling the static method ```createFromGlobals```.

```php
$request = Request::createFromGlobals();
```