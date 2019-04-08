<?php declare(strict_types=1);

namespace Capsule\Body;

/**
 * @package Capsule\Body
 * 
 * Passes raw XML data through.
 * 
 * Sets the Content-Type as "application/xml" by default.
 */
class XmlBody extends BufferBody implements BodyInterface
{
    /**
     * @inheritDoc
     */
    protected $contentType = "application/xml";
    
    /**
     * XmlBody constructor.
     *
     * @param string $data
     * @param string|null $contentType
     */
    public function __construct(string $data = "", string $contentType = null)
    {
        $this->buffer = $data;

        if( $contentType ){
            $this->contentType = $contentType;
        }
    }
}