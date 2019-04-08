<?php declare(strict_types=1);

namespace Capsule\Body;

use Capsule\Stream\FileStream;
use Psr\Http\Message\StreamInterface;


class FileUploadBody extends BufferBody implements BodyInterface
{
    /**
     * File contents to upload.
     *
     * @var StreamInterface
     */
    protected $stream;

    /**
     * File name to use in multipart/form-data content disposition.
     *
     * @var string
     */
    protected $fileName;

    /**
     * The file's content type.
     *
     * @var string
     */
    protected $fileContentType;

    /**
     * The content type.
     *
     * @var string
     */
    protected $contentType = "multipart/form-data;boundary=--0425150128197707252015Z";

    /**
     * Boundary
     *
     * @var string
     */
    protected $boundary = "--0425150128197707252015Z";

    /**
     * FileUpload constructor
     *
     * @param StreamInterface|string $file StreamInterface instance of file or the full path of file to open.
     * @param string $fileName File name to send with request.
     * @param string $contentType File mime content type.
     * @param string 
     */
    public function __construct(string $file, string $fileName, string $contentType)
    {
        if( ($file instanceof StreamInterface) === false ){
            $this->stream = new FileStream(
                fopen($file, "r")
            );

            $this->fileName = $fileName ?? basename($file);
            $this->fileContentType = $contentType ?? mime_content_type($file);
        }

        else {
            $this->stream = $file;
            $this->fileName = $fileName ?? "filename";
            $this->fileContentType = $file->getMetadata();
        }

        $this->buffer = $this->getMultiPart($this->boundary, $name ?? "file") . "\n--{$this->boundary}--";
    }

    /**
     * @inheritDoc
     */
    public function getMultiPart(string $boundary, string $name): string
    {
        // Rewind the stream, just in case we're at the end.
        if( $this->stream->isSeekable() ){
            $this->stream->rewind();
        }
        
        // Build out multi-part
        $multipart = "\r\n{$boundary}\r\n";
        $multipart .= "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$this->fileName}\"\r\n";
        $multipart .= "Content-Type: {$this->stream->getContentType()}\r\n\r\n";
        $multipart .= $this->stream->getContents();

        return $multipart;
    }
}