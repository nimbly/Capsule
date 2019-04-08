<?php declare(strict_types=1);

namespace Capsule\Body;

/**
 * @package Capsule\Body
 * 
 * Format a key => value pair array into a Form Urlencoded string.
 * 
 * Sets Content-Type as "application/x-www-form-urlencoded" by default.
 * 
 */
class FormBody extends BufferBody implements BodyInterface
{
    /**
     * @inheritDoc
     */
    protected $contentType = "application/x-www-form-urlencoded";

    /**
     * FormBody constructor.
     *
     * @param array $data
     * @param string|null $contentType
     */
    public function __construct(array $data, string $contentType = null)
    {
        $this->buffer = http_build_query($data, "n", "&", PHP_QUERY_RFC1738);

        if( $contentType ){
            $this->contentType = $contentType;
        }
    }

    /**
     * @inheritDoc
     */
    public function getMultiPart(string $boundary, ?string $name = null): string
    {
        // Convert the form data back into an array
        parse_str($this->buffer, $formFields);

        $multiPart = "";

        foreach( $formFields as $name => $value ){
            $multiPart .= "\r\n{$boundary}\r\n";
            $multiPart .= 'Content-Disposition: form-data; name="' . $name . '"' . "\r\n\r\n";
            $multiPart .= $value;
        }

        return $multiPart;
    }
}