<?php

namespace Shuttle\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Body\BufferBody;

/**
 * @covers Capsule\Body\BufferBody
 */
class BufferBodyTest extends TestCase
{
    public function test_setting_body_in_buffer_body_constructor()
    {
        $bufferBody = new BufferBody("OK", "text/plain");
        $this->assertEquals("OK", $bufferBody->getContents());
    }

    public function test_setting_content_type_in_buffer_body_constructor()
    {
        $bufferBody = new BufferBody("OK", "text/plain");
        $this->assertEquals("text/plain", $bufferBody->getContentType());
    }
}