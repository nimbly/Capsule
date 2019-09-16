<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;


abstract class MessageAbstract implements MessageInterface
{
    /**
     * Message version
     *
     * @var string
     */
    protected $version = "1.1";

    /**
     * Message headers
     *
     * @var array<string, array<string>>
     */
    protected $headers = [];

    /**
     * Message body
     *
     * @var StreamInterface
     */
	protected $body;

	/**
	 * Allowed HTTP versions
	 *
	 * @var array<string>
	 */
	private $allowedVersions = [
		"1.1", "1.0", "2", "2.0"
	];

    /**
     * @inheritDoc
	 * @return string
     */
    public function getProtocolVersion()
    {
        return $this->version;
    }

    /**
     * @inheritDoc
     */
    public function withProtocolVersion($version)
    {
        if( !\in_array($version, $this->allowedVersions) ){
            throw new \Exception("Invalid protocol version {$version}");
        }

        $instance = clone $this;
        $instance->version = $version;
        return $instance;
    }

    /**
     * Find a header by its case-insensitive name.
     *
     * @param string $name
     * @return string|null
     */
    private function findHeaderKey(string $name): ?string
    {
        foreach( $this->headers as $key => $value ){
            if( \strtolower($name) === \strtolower($key) ){
                return $key;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
	 * @return array<string, array<string>>
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @inheritDoc
	 * @return boolean
     */
    public function hasHeader($name)
    {
        return ($this->findHeaderKey($name) !== null);
    }

    /**
     * @inheritDoc
     */
    public function getHeader($name)
    {
        if( ($key = $this->findHeaderKey($name)) !== null ){
            return $this->headers[$key];
        }

        return [];
    }

    /**
     * @inheritDoc
	 * @return string
     */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);

        if( empty($header) ){
            return "";
        }

        return \implode(",", $header);
    }

    /**
     * @inheritDoc
     */
    public function withHeader($name, $value)
    {
		$instance = clone $this;

		if( !\is_array($value) ){
			$value = [$value];
		}

        $instance->headers[$name] = $value;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withAddedHeader($name, $value)
    {
        if( ($key = $this->findHeaderKey($name)) === null ){
            $key = $name;
        }

		$instance = clone $this;

		if( !\is_array($value) ){
			$value = [$value];
		}

        $instance->headers[$key] = \array_merge(
			$instance->headers[$key] ?? [],
			$value
		);

        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withoutHeader($name)
    {
        if( ($key = $this->findHeaderKey($name)) === null ){
            return $this;
        }

        $instance = clone $this;
        unset($instance->headers[$key]);
        return $instance;
    }

    /**
     * Mass assign headers.
     *
     * @param array<string, string> $headers
	 * @throws RuntimeException
     * @return void
     */
    protected function setHeaders(array $headers): void
    {
        foreach( $headers as $name => $value ){
            $this->headers[$name][] = $value;
		}
	}

	/**
	 * Set the Host header.
	 *
	 * @param string $host
	 * @param int|null $port
	 * @return void
	 */
	protected function setHostHeader(string $host, ?int $port = null): void
	{
		if( ($key = $this->findHeaderKey('Host')) ){
			unset($this->headers[$key]);
		}

		if( $port ){
			$host .= ":{$port}";
		}

		$this->headers = \array_merge(
			['Host' => [$host]],
			$this->headers ?? []
		);
	}

    /**
     * @inheritDoc
     */
    public function getBody(): ?StreamInterface
    {
        return $this->body;
    }

    /**
     * @inheritDoc
     */
    public function withBody(StreamInterface $body)
    {
        $instance = clone $this;
        $instance->body = $body;
        return $instance;
    }
}