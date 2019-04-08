<?php declare(strict_types=1);

namespace Capsule\Body;

/**
 * @package Capsule\Body
 * 
 * Formats an associative array as a JSON encoded string.
 * 
 * Sets the Content-Type as "application/json" by default.
 */
class JsonBody extends BufferBody implements BodyInterface
{
    /**
     * @inheritDoc
     */
    protected $contentType = "application/json";

    /**
     * JsonBody constructor.
     *
     * @param array $data
     * @param string|null $contentType
     */
    public function __construct(array $data, string $contentType = null)
    {
        if( ($json = json_encode($data)) === false ){
            throw new \Exception("Invalid JSON");
        }

        $this->buffer = $json;

        if( $contentType ){
            $this->contentType = $contentType;
        }
    }
}