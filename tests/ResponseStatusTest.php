<?php

namespace Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\ResponseStatus;

/**
 * @package Capsule\Tests
 *
 * @covers Capsule\ResponseStatus
 */
class ResponseStatusTest extends TestCase
{
    public function test_supported_response_code_returns_phrase()
    {
        $this->assertNotNull(ResponseStatus::getPhrase(ResponseStatus::CREATED));
    }

    public function test_unsupported_response_code_returns_null()
    {
        $this->assertNull(ResponseStatus::getPhrase(420));
    }
}