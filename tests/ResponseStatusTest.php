<?php

namespace Nimbly\Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Nimbly\Capsule\ResponseStatus;

/**
 * @covers Nimbly\Capsule\ResponseStatus
 */
class ResponseStatusTest extends TestCase
{
	public function test_supported_response_code_returns_phrase()
	{
		$this->assertEquals(
			"Ok",
			ResponseStatus::getPhrase(ResponseStatus::OK)
		);
	}

	public function test_unsupported_response_code_returns_empty_string()
	{
		$this->assertEmpty(ResponseStatus::getPhrase(1));
	}
}