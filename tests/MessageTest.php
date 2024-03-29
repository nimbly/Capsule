<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Request;
use Nimbly\Capsule\Stream\BufferStream;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers Nimbly\Capsule\MessageAbstract
 * @covers Nimbly\Capsule\Request
 * @covers Nimbly\Capsule\Stream\BufferStream
 * @covers Nimbly\Capsule\Factory\UriFactory
 * @covers Nimbly\Capsule\Uri
 * @covers Nimbly\Capsule\Factory\StreamFactory
 */
class MessageTest extends TestCase
{
	public function test_valid_protocol_versions_allowed(): void
	{
		$request = new Request("get", "/foo");
		$request = $request->withProtocolVersion("2.0");
		$this->assertEquals("2.0", $request->getProtocolVersion());
	}

	public function test_protocol_version_not_allowed(): void
	{
		$this->expectException(\Exception::class);

		$request = new Request("get", "/foo");
		$request = $request->withProtocolVersion("2.1");
	}

	public function test_with_protocol_version_is_imuutable(): void
	{
		$request = new Request("get", "/foo");
		$newRequest = $request->withProtocolVersion("2.0");
		$this->assertNotSame($request, $newRequest);
	}

	public function test_with_body_saves_data(): void
	{
		$request = new Request("get", "/foo");
		$request = $request->withBody(new BufferStream("test body"));
		$this->assertNotEmpty($request->getBody());
	}

	public function test_with_body_is_immutable(): void
	{
		$request = new Request("get", "/foo");
		$newRequest = $request->withBody(new BufferStream("test body"));
		$this->assertNotSame($request, $newRequest);
	}

	public function test_get_header_returns_array(): void
	{
		$request = new Request("get", "/foo", null, ["Content-Type" => "application/json"]);
		$this->assertTrue(\is_array($request->getHeader("Content-Type")));
	}

	public function test_get_header_returns_empty_array_if_header_not_found(): void
	{
		$request = new Request("get", "/foo");
		$header = $request->getHeader("X-Foo");

		$this->assertEquals([], $header);
	}

	public function test_get_header_line_returns_empty_string_if_header_not_found(): void
	{
		$request = new Request("get", "/foo");

		$this->assertEquals("", $request->getHeaderLine("X-Foo"));
	}

	public function test_get_headers_returns_all_headrs(): void
	{
		$request = new Request("get", "/foo");

		$request = $request->withHeader("X-Foo", "FooHeader");
		$request = $request->withHeader("X-Bar", "BarHeader");

		$this->assertEquals([
			"X-Foo" => ["FooHeader"],
			"X-Bar" => ["BarHeader"]
		], $request->getHeaders());
	}

	public function test_with_added_header_for_header_that_does_not_exist(): void
	{
		$request = new Request("get", "/foo");
		$request = $request->withAddedHeader("X-Foo", "FooHeader");

		$this->assertEquals("FooHeader", $request->getHeaderLine("X-Foo"));
	}

	public function test_with_header_replaces_existing_header(): void
	{
		$request = new Request("get", "/foo", null, ["Content-Type" => "application/json"]);
		$request = $request->withHeader("content-type", "text/html");

		$this->assertEquals("text/html", $request->getHeaderLine("Content-Type"));
	}

	public function test_with_added_header_adds_new_value(): void
	{
		$request = new Request("get", "/foo", null, ["X-Foo" => "bar"]);
		$request = $request->withAddedHeader("X-Foo", "baz");

		$this->assertEquals(2, count($request->getHeader("X-Foo")));
	}

	public function test_header_names_are_case_insensitive(): void
	{
		$request = new Request("get", "/foo", null, ["X-Foo" => "bar"]);
		$this->assertNotEmpty($request->getHeader("x-foo"));
	}

	public function test_without_header_removes_header(): void
	{
		$request = new Request("get", "/foo", null, ["X-Foo" => "bar"]);
		$request = $request->withoutHeader("X-Foo");

		$this->assertFalse($request->hasHeader("X-Foo"));
	}

	public function test_without_header_returns_same_instance_if_header_not_found(): void
	{
		$request = new Request("get", "/foo");

		$newRequest = $request->withoutHeader("X-Foo");

		$this->assertSame($request, $newRequest);
	}

	public function test_set_host_header_makes_host_header_first_in_array(): void
	{
		$request = new Request(
			"get",
			"http://example.org",
			null,
			[
				"Content-Type" => "application/json",
				"Accept" => "application/json"
			]
		);

		$reflection = new ReflectionClass($request);
		$property = $reflection->getProperty("headers");
		$property->setAccessible(true);

		$headers = $property->getValue($request);

		$this->assertEquals(
			["Host" => ["example.org"]],
			\array_slice($headers, 0 , 1)
		);
	}

	public function test_set_host_header_removes_previous_host_header(): void
	{
		$request = new Request("get", "http://example.org");

		$reflection = new ReflectionClass($request);
		$method = $reflection->getMethod("setHostHeader");
		$method->setAccessible(true);
		$method->invokeArgs($request, ["capsule.org", 8080]);

		$this->assertEquals([
			"capsule.org:8080"
		], $request->getHeader("Host"));
	}
}