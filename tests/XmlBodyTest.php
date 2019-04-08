<?php

namespace Shuttle\Tests;

use PHPUnit\Framework\TestCase;
use Capsule\Body\XmlBody;

/**
 * @covers Capsule\Body\XmlBody
 * @covers Capsule\Body\BufferBody
 * @covers Capsule\Stream\BufferStream
 */
class XmlBodyTest extends TestCase
{
    public function test_default_content_type()
    {
        $xmlBody = new XmlBody("");
        $this->assertEquals("application/xml", $xmlBody->getContentType());
    }

    public function test_override_content_type()
    {
        $xmlBody = new XmlBody("", "application/xhtml+xml");
        $this->assertEquals("application/xhtml+xml", $xmlBody->getContentType());
    }

    public function test_form_body_transformation()
    {
        $content = <<<XML
<books>
        <book>
            <title>Breakfast Of Champions</title>
            <author>Kurt Vonnegut</author>
        </book>

        <book>
            <title>Time's Arrow</title>
            <author>Martin Amis</title>
        </book>
</books>
XML;
        
        $xmlBody = new XmlBody($content);

        $this->assertEquals($content, $xmlBody->getContents());
    }
}