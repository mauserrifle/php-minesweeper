<?php
namespace Minesweeper;

class Arr {
	/**
	 * Retrieve a single key from an array. If the key does not exist in the
	 * array, the default value will be returned instead.
	 *
	 *     // Get the value "username" from $_POST, if it exists
	 *     $username = Arr::get($_POST, 'username');
	 *
	 *     // Get the value "sorting" from $_GET, if it exists
	 *     $sorting = Arr::get($_GET, 'sorting');
	 *
	 * This function is from the Kohana project (http://kohanaframework.org/).
	 *
	 * @param   array   $array      array to extract from
	 * @param   string  $key        key name
	 * @param   mixed   $default    default value
	 * @return  mixed
	 */
	public static function get($array, $key, $default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}
}