<?php

namespace Nimbly\Capsule\Tests;

use PHPUnit\Framework\TestCase;
use Nimbly\Capsule\ResponseStatus;

/**
 * @covers Nimbly\Capsule\ResponseStatus
 */
class ResponseStatusTest extends TestCase
{
	public function test_all_supported_response_codes_return_phrase()
	{
		foreach( ResponseStatus::cases() as $case ){
			$this->assertNotEmpty($case->getPhrase());
		}
	}
}