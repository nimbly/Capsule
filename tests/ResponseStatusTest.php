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
		foreach( ResponseStatus::cases() as $case ){
			$this->assertNotNull(
				$case->getPhrase()
			);
		}
	}
}