<?php

namespace Shuttle\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Body\JsonBody;

/**
 * @covers Capsule\Body\JsonBody
 * @covers Capsule\Body\BufferBody
 */
class JsonBodyTest extends TestCase
{
    public function test_default_content_type()
    {
        $jsonBody = new JsonBody([]);

        $this->assertEquals("application/json", $jsonBody->getContentType());
    }

    public function test_override_content_type()
    {
        $jsonBody = new JsonBody([], "application/vnd.api+json");

        $this->assertEquals("application/vnd.api+json", $jsonBody->getContentType());
    }

    public function test_json_encoding()
    {
        $jsonBody = new JsonBody(
            [
                "name" => "John Doe",
                "email" => "jdoe@example.com",
            ]
        );

        $this->assertEquals('{"name":"John Doe","email":"jdoe@example.com"}', $jsonBody->getContents());
    }
}