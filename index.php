<?php

use Capsule\Request;

require __DIR__ . '/vendor/autoload.php';
$request = Request::makeFromGlobals();
print_r((string) $request->getUri());