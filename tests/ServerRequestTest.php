<?php

namespace Nimbly\Capsule\Tests;

use Nimbly\Capsule\Factory\UriFactory;
use Nimbly\Capsule\ServerRequest;
use Nimbly\Capsule\Stream\BufferStream;
use Nimbly\Capsule\UploadedFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers Nimbly\Capsule\ServerRequest
 * @covers Nimbly\Capsule\Request
 * @covers Nimbly\Capsule\Factory\UriFactory
 * @covers Nimbly\Capsule\Uri
 * @covers Nimbly\Capsule\Stream\BufferStream
 * @covers Nimbly\Capsule\MessageAbstract
 * @covers Nimbly\Capsule\UploadedFile
 * @covers Nimbly\Capsule\Factory\StreamFactory
 */
class ServerRequestTest extends TestCase
{
	private function makeRequest(): ServerRequest
	{
		$serverRequest = new ServerRequest(
			"GET",
			"http://example.org/foo/bar?q=search",
			null,
			//["name" => "Test User", "email" => "test@example.com"],
			//"{"name": "Test User", "email": "test@example.com"}",
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
					new BufferStream("Capsule!"),
					"text/plain",
					"temp_file.name",
					100,
					UPLOAD_ERR_OK
				)
			],
			[],
			"1.1"
		);

		return $serverRequest->withParsedBody(["name" => "Test User", "email" => "test@example.com"]);
	}

	public function test_create_with_uri_instance(): void
	{
		$uri = UriFactory::createFromString("http://example.org/foo/bar?q=search");

		$request = new ServerRequest("get", $uri);

		$this->assertSame(
			$uri,
			$request->getUri()
		);
	}

	public function test_create_with_string_body_creates_buffer_stream(): void
	{
		$request = new ServerRequest(
			"post",
			"http://example.org/foo/bar?q=search",
			\json_encode(["email" => "test@example.com", "name" => "Testy Test"])
		);

		$this->assertInstanceOf(
			BufferStream::class,
			$request->getBody()
		);
	}

	public function test_get_server_params(): void
	{
		$request = $this->makeRequest();

		$request = new ServerRequest(
			"get",
			"http://example.org/foo/bar?q=search",
			null,
			[],
			[],
			[],
			[],
			[
				"SERVER_NAME" => "Capsule",
				"SERVER_VERSION" => 1.0,
			]
		);

		$this->assertEquals(
			[
				"SERVER_NAME" => "Capsule",
				"SERVER_VERSION" => 1.0,
			],
			$request->getServerParams()
		);
	}

	public function test_get_cookie_params(): void
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

	public function test_with_cookie_params(): void
	{
		$request = $this->makeRequest();

		$request = $request->withCookieParams([
			"cookie3" => "value3",
			"cookie4" => "value4"
		]);

		$this->assertEquals(
			[
				"cookie3" => "value3",
				"cookie4" => "value4"
			],
			$request->getCookieParams()
		);
	}

	public function test_get_query_params(): void
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				"query1" => "value1",
				"query2" => "value2",
				"q" => "search"
			],
			$request->getQueryParams()
		);
	}

	public function test_with_query_params(): void
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

	public function test_get_uploaded_files(): void
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				new UploadedFile(
					new BufferStream("Capsule!"),
					"text/plain",
					"temp_file.name",
					100,
					UPLOAD_ERR_OK
				)
			],

			$request->getUploadedFiles()
		);
	}

	public function test_with_uploaded_files_is_immutable(): void
	{
		$request = $this->makeRequest();

		$newRequest = $request->withUploadedFiles([
			new UploadedFile(new BufferStream, "appliication/json", "foo", 101, UPLOAD_ERR_OK),
			new UploadedFile(new BufferStream, "appliication/json", "bar", 201, UPLOAD_ERR_NO_FILE)
		]);

		$this->assertNotSame(
			$request,
			$newRequest
		);

		$this->assertEquals(
			[
				new UploadedFile(new BufferStream, "appliication/json", "foo", 101, UPLOAD_ERR_OK),
				new UploadedFile(new BufferStream, "appliication/json", "bar", 201, UPLOAD_ERR_NO_FILE)
			],
			$newRequest->getUploadedFiles()
		);
	}

	public function test_with_parsed_body(): void
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

	public function test_with_attribute(): void
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

	public function test_get_attributes(): void
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

	public function test_without_attribute(): void
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

	public function test_has_body_param(): void
	{
		$request = $this->makeRequest();

		$this->assertTrue($request->hasBodyParam("name"));
	}

	public function test_has_query_param(): void
	{
		$request = $this->makeRequest();

		$this->assertTrue($request->hasQueryParam("query1"));
	}

	public function test_get_query_param(): void
	{
		$request = $this->makeRequest();

		$this->assertEquals("value1", $request->getQueryParam("query1"));
	}

	public function test_get_body_param_from_array_parsed_body(): void
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io"
			]
		);

		$this->assertEquals("test@nimbly.io", $request->getBodyParam("email"));
	}

	public function test_get_body_param_from_object_parsed_body(): void
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			(object) [
				"email" => "test@nimbly.io"
			]
		);

		$this->assertEquals("test@nimbly.io", $request->getBodyParam("email"));
	}

	public function test_get_body_param_returns_null_if_not_found(): void
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io"
			]
		);

		$this->assertNull($request->getBodyParam("id"));
	}

	public function test_only_body_params(): void
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io",
				"name" => "Bob Smith",
				"age" => 42
			]
		);

		$this->assertEquals(
			[
				"name" => "Bob Smith",
				"age" => 42
			],
			$request->onlyBodyParams(["name", "age"])
		);
	}

	public function test_except_body_params(): void
	{
		$request = $this->makeRequest();

		$request = $request->withParsedBody(
			[
				"email" => "test@nimbly.io",
				"name" => "Bob Smith",
				"age" => 42
			]
		);

		$this->assertEquals(
			[
				"email" => "test@nimbly.io",
				"name" => "Bob Smith"
			],
			$request->exceptBodyParams(["age"])
		);
	}

	public function test_has_uploaded_file(): void
	{
		$request = $this->makeRequest();

		$uploadedFile = new UploadedFile(
			new BufferStream("Capsule!")
		);

		$request = $request->withUploadedFiles([
			"file" => $uploadedFile
		]);

		$this->assertTrue(
			$request->hasUploadedFile("file")
		);
	}

	public function test_get_uploaded_file(): void
	{
		$request = $this->makeRequest();

		$uploadedFile = new UploadedFile(
			new BufferStream
		);

		$request = $request->withUploadedFiles([
			"file" => $uploadedFile
		]);

		$this->assertSame(
			$uploadedFile,
			$request->getUploadedFile("file")
		);
	}

	public function test_get_all_params(): void
	{
		$request = $this->makeRequest();

		$this->assertEquals(
			[
				"name" => "Test User",
				"email" => "test@example.com",
				"query1" => "value1",
				"query2" => "value2",
				"q" => "search"
			],
			$request->getAllParams()
		);
	}
}