<?php

namespace Capsule\Tests;

use Capsule\ServerRequest;
use Capsule\UploadedFile;
use Capsule\Uri;
use PHPUnit\Framework\TestCase;

/**
 * @covers Capsule\ServerRequest
 * @covers Capsule\Request
 * @covers Capsule\Uri
 * @covers Capsule\Stream\BufferStream
 * @covers Capsule\MessageAbstract
 * @covers Capsule\UploadedFile
 */
class ServerRequestTest extends TestCase
{
	public function makeRequest(): ServerRequest
	{
		return ServerRequest::create(
			"GET",
			"http://example.org/foo/bar?q=search",
			'{"name": "Test User", "email": "test@example.com"}',
			[
				"query1" => "value1",
				"query2" => "value2"

			],
			[
				"Content-Type" => "application/json",
				"User-Agent" => "Capsule 1.0"
			],
			[
				"cookie1" => "value1",
				"cookie2" => "value2"
			],
			[
				new UploadedFile(
					'file1',
					'text/plain',
					'temp_file.name',
					100,
					UPLOAD_ERR_OK
				)
			],
			"1.2.3.4",
			"1.1"
		);
	}

	public function test_get_server_params()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			$_SERVER,
			$request->getServerParams()
		);
	}

	public function test_get_cookie_params()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				"cookie1" => "value1",
				"cookie2" => "value2"
			],
			$request->getCookieParams()
		);
	}

	public function test_with_cookie_params()
	{
		$request = $this->makeRequest();

		$request = $request->withCookieParams([
			'cookie3' => 'value3',
			'cookie4' => 'value4'
		]);

		$this->assertEquals(
			[
				'cookie3' => 'value3',
				'cookie4' => 'value4'
			],
			$request->getCookieParams()
		);
	}

	public function test_get_query_params()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				"query1" => "value1",
				"query2" => "value2"
			],
			$request->getQueryParams()
		);
	}

	public function test_with_query_params()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withQueryParams([
			"query3" => "value3",
			"query4" => "value4"
		]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				"query3" => "value3",
				"query4" => "value4"
			],
			$newRequest->getQueryParams()
		);
	}

	public function test_get_uploaded_files()
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				new UploadedFile(
					'file1',
					'text/plain',
					'temp_file.name',
					100,
					UPLOAD_ERR_OK
				)
			],

			$request->getUploadedFiles()
		);
	}

	public function test_with_uploaded_files()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withUploadedFiles([
			new UploadedFile("example1.file", "appliication/json", "foo", 101, UPLOAD_ERR_OK),
			new UploadedFile("example2.file", "appliication/json", "bar", 201, UPLOAD_ERR_NO_FILE)
		]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				new UploadedFile("example1.file", "appliication/json", "foo", 101, UPLOAD_ERR_OK),
				new UploadedFile("example2.file", "appliication/json", "bar", 201, UPLOAD_ERR_NO_FILE)
			],
			$newRequest->getUploadedFiles()
		);
	}

	public function test_get_parsed_body()
	{
		$request = $this->makeRequest();

		$this->assertEquals([
			"name" => "Test User",
			"email" => "test@example.com"
		], $request->getParsedBody());
	}

	public function test_with_parsed_body()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withParsedBody(["foo" => "bar"]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				"foo" => "bar"
			],
			$newRequest->getParsedBody()
		);
	}

	public function test_with_attribute()
	{
		$request = $this->makeRequest();

		$newRequest = $request->withAttribute("attr1", "value1");

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			"value1",
			$newRequest->getAttribute("attr1")
		);
	}

	public function test_get_attributes()
	{
		$request = $this->makeRequest();

		$request = $request->withAttribute("attr1", "value1");
		$request = $request->withAttribute("attr2", "value2");

		$this->assertEquals(
			[
				"attr1" => "value1",
				"attr2" => "value2"
			],
			$request->getAttributes()
		);
	}

	public function test_without_attribute()
	{
		$request = $this->makeRequest();

		$request = $request->withAttribute("attr1", "value1");
		$request = $request->withAttribute("attr2", "value2");

		$this->assertEquals(
			[
				"attr1" => "value1",
				"attr2" => "value2"
			],
			$request->getAttributes()
		);

		$request = $request->withoutAttribute("attr2");

		$this->assertEquals(
			[
				"attr1" => "value1"
			],
			$request->getAttributes()
		);
	}
}