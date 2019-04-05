# Capsule
Simple PSR-7 HTTP message implementation -- no bells, no whistles.

## Using globals
You can create a ```Request``` instance from PHP's ```$_SERVER``` super global by calling the static method ```createFromGlobals```.

```php
$request = Request::createFromGlobals();
```