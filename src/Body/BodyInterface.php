<?php declare(strict_types=1);

namespace Capsule\Body;

interface BodyInterface
{
    /**
     * Get the body's Content-Type header value.
     *
     * @return string
     */
    public function getContentType(): string;

    /**
     * Get the content disposition for multi part.
     *
     * @param string $boundary
     * @param string $name
     * @return string
     */
    public function getMultiPart(string $boundary, string $name): string;
}