<?php declare(strict_types=1);

namespace Capsule;

use Psr\Http\Message\UriInterface;

/**
 * @psalm-suppress MissingConstructor
 */
class Uri implements UriInterface
{
    /**
     * URI scheme (http or https)
     *
     * @var string|null
     */
    protected $scheme;

    /**
     * Request host
     *
     * @var string|null
     */
    protected $host;

    /**
     * Port number
     *
     * @var int|null
     */
    protected $port;

    /**
     * Username
     *
     * @var string|null
     */
    protected $username;

    /**
     * Password
     *
     * @var string|null
     */
    protected $password;

    /**
     * Path
     *
     * @var string|null
     */
    protected $path;

    /**
     * Query
     *
     * @var string|null
     */
    protected $query;

    /**
     * Fragment
     *
     * @var string|null
     */
	protected $fragment;

    /**
     * @inheritDoc
     */
    public function getScheme()
    {
        return $this->scheme ?? "";
    }

    /**
     * @inheritDoc
     */
    public function getAuthority()
    {
        if( empty($this->username) && empty($this->password) ){
            return "";
        }

        return "{$this->username}:{$this->password}@{$this->host}:{$this->port}";
    }

    /**
     * @inheritDoc
     */
    public function getUserInfo()
    {
        $userInfo = "";

        if( $this->username ){
            $userInfo = $this->username;
        }

        if( $this->password ){
            $userInfo .= ":{$this->password}";
        }

        return $userInfo;
    }

    /**
     * @inheritDoc
     */
    public function getHost()
    {
        return $this->host ?? "";
    }

    /**
     * @inheritDoc
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @inheritDoc
     */
    public function getPath()
    {
        return $this->path ?? "";
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        return $this->query ?? "";
    }

    /**
     * @inheritDoc
     */
    public function getFragment()
    {
        return $this->fragment ?? "";
    }

    /**
     * @inheritDoc
     */
    public function withScheme($scheme)
    {
        $instance = clone $this;
        $instance->scheme = \strtolower($scheme);
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withUserInfo($user, $password = null)
    {
        $instance = clone $this;
        $instance->username = $user;
        $instance->password = $password ?? "";
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withHost($host)
    {
        $instance = clone $this;
        $instance->host = $host;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withPort($port)
    {
        $instance = clone $this;
        $instance->port = $port;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withPath($path)
    {
        $instance = clone $this;
        $instance->path = $path;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function withQuery($query)
    {
        $instance = clone $this;
        $instance->query = $query;
        return $instance;
    }


    /**
     * @inheritDoc
     */
    public function withFragment($fragment)
    {
        $instance = clone $this;
        $instance->fragment = $fragment;
        return $instance;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        $url = "{$this->scheme}://";

        if( $this->username || $this->password ){
            $url .= "{$this->username}:{$this->password}@";
        }

        $url .= $this->host ?? "";

        if( $this->port ){
            $url .= ":{$this->port}";
        }

        $url.= ($this->path ?? "/");

        if( $this->query ){
            $url .= "?{$this->query}";
        }

        if( $this->fragment ){
            $url .= "#{$this->fragment}";
        }

        return $url;
    }
}