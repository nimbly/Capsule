<?php

namespace Capsule\Body;

use Capsule\Body\BodyInterface;
use Capsule\Body\FileUpload;
use Capsule\Body\FormBody;

class MultipartFormBody extends BufferBody
{
    /**
     * Multi-part content type, without boundary.
     *
     * @var string
     */
    protected $contentType = "multipart/form-data";

    /**
     * Boundary
     *
     * @var string
     */
    protected $boundary = "--0425150128197707252015Z";

    /**
     * MultipartFormBody constructor.
     *
     * @param array<BodyInterface> $parts
     */
    public function __construct(array $parts)
    {
        foreach( $parts as $name => $part ){

            $this->write(
                $part->getMultiPart($this->boundary, is_int($name) ? null : $name)
            );
        }

        $this->write("\r\n{$this->boundary}--\r\n");
    }

    /**
     * @inheritDoc
     */
    public function getContentType(): string
    {
        return "{$this->contentType};boundary={$this->boundary}";
    }
}