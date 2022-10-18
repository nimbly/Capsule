<?php

require __DIR__ . "/../vendor/autoload.php";

/**
 * Disable warnings in order to test failed opening of files
 * with \fopen().
 */
\error_reporting(E_ALL & ~E_WARNING);