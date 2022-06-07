<?php

namespace Nimbly\Capsule;

use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
	public function __construct(
		protected string $scheme,
		protected string $host,
		protected ?string $path = null,
		protected ?int $port = null,
		protected ?string $username = null,
		protected ?string $password = null,
		protected ?string $query = null,
		protected ?string $fragment = null
	)
	{
	}

	/**
	 * @inheritDoc
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * @inheritDoc
	 */
	public function getAuthority()
	{
		if( empty($this->username) && empty($this->password) ){
			return "";
		}

		return \sprintf(
			"%s:%s@%s:%s",
			$this->username ?? "",
			$this->password ?? "",
			$this->host,
			$this->port ?? ""
		);
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
		return $this->host;
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

		$url .= $this->host;

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