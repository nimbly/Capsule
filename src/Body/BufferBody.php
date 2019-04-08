<?php declare(strict_types=1);

namespace Capsule\Body;

use Capsule\Stream\BufferStream;

/**
 * @package Capsule\Body
 * 
 * A generic string buffer body with no formatting/serialization.
 * 
 * Sets the Content-Type as "text/plain" by default.
 * 
 */
class BufferBody extends BufferStream implements BodyInterface
{
    /**
     * Content-Type header data.
     * 
     * E.g. application/json
     *
     * @var string
     */
    protected $contentType = "text/plain";

    /**
     * BufferBody constructor.
     *
     * @param string $data
     * @param string|null $contentType
     */
    public function __construct(string $data, string $contentType = null)
    {
        $this->buffer = $data;

        if( $contentType ){
            $this->contentType = $contentType;
        }
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return $this->contentType;
    }

    /**
     * @inheritDoc
     */
    public function getMultiPart(string $boundary, string $name): string
    {
        $multipart  = "\r\n{$boundary}\r\n";
        $multipart .= "Content-Disposition: form-data; name=\"{$name}\"\r\n";
        $multipart .= "Content-Type: {$this->getContentType()}\r\n\r\n";
        $multipart .= $this->buffer;

        return $multipart;
    }
}