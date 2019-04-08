<?php

namespace Capsule\Body;

use XMLWriter;

class XmlWriterBody extends BufferBody implements BodyInterface 
{
    /**
     * @inheritDoc
     */
    protected $contentType = "application/xml";

    /**
     * XmlWriterBody constructor.
     *
     * @param XMLWriter $xmlWriter
     * @param string|null $contentType
     */
    public function __construct(XMLWriter $xmlWriter, string $contentType = null)
    {
        $this->buffer = $xmlWriter->flush();

        if( $contentType ){
            $this->contentType = $contentType;
        }
    }
}