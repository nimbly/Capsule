<?php

/**
 * A compatibility layer for functions not available in all PHP versions.
 */
if( \function_exists("array_find") === false ){

	/**
	 * Finds the first key in an array that satisfies the given callback.
	 *
	 * @param array<string,mixed> $array
	 * @param callable $callback
	 * @return string|null Returns the key if found, null otherwise.
	 */
	function array_find(array $array, callable $callback): ?string
	{
		foreach( $array as $key => $value ){
			if( $callback($value) ){
				return $key;
			}
		}

		return null;
	}
}