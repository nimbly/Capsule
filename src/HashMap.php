<?php

namespace Capsule;

use Countable;
use Iterator;

class Hashmap implements Iterator, Countable
{
	/**
	 * Map of items.
	 *
	 * @var array
	 */
	protected $map;

	/**
	 * HashMap constructor.
	 *
	 * @param array<string, mixed> $map
	 */
	public function __construct(array $map = [])
	{
		$this->map = $map;
	}

	/**
	 * Add an item to the HashMap
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function add(string $key, $value): void
	{
		$this->map[$key] = $value;
	}

	/**
	 * Remove an item from the HashMap
	 *
	 * @param string $key
	 * @return void
	 */
	public function remove(string $key): void
	{
		if( $this->has($key) ){
			unset($this->map[$key]);
		}
	}

	/**
	 * Check if HashMap contains a specific key.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function has(string $key): bool
	{
		return \array_key_exists($key, $this->map);
	}

	/**
	 * Get an item from the HashMap.
	 *
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get(string $key, $default = null)
	{
		return $this->map[$key] ?? $default;
	}

	/**
	 * Get all items from the HashMap.
	 *
	 * @return array
	 */
	public function all(): array
	{
		return $this->map;
	}

	/**
	 * Get all items from the HashMap.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $this->all();
	}

	/**
	 *
	 * Countable interface implementations.
	 *
	 */

	/**
	 * Get the count of items in the HashMap.
	 *
	 * @return integer
	 */
	public function count(): int
	{
		return \count($this->map);
	}

	/**
	 *
	 * Iterator interface implementations.
	 *
	 */

	public function current()
	{
		return \current($this->map);
	}

	public function key()
	{
		return \key($this->map);
	}

	public function next()
	{
		return \next($this->map);
	}

	public function rewind()
	{
		return \reset($this->map);
	}

	public function valid()
	{
		return $this->key() !== null;
	}
}