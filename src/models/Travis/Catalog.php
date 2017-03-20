<?php

namespace Travis;

use SuperClosure\Serializer;
use Travis\Catalog\Model;
use Travis\Catalog\API;
use Travis\Date;

class Catalog
{
	/**
	 * Return a cached API response, or a new one.
	 *
	 * @param	string	$name
	 * @param	closure	$closure
	 * @param	string	$age_limit
	 * @param	string	$id
	 * @param	string	$endpoint
	 * @return	array
	 */
	public static function lookup($name, $closure, $age_limit = null, $id = null, $endpoint = null)
	{
		// calculate hash
		$hash = static::hash($name, $closure, $id);

		// load from storage
		$check = static::get($hash, $endpoint);

		// if found...
		if ($check)
		{
			// if age limit...
			if ($age_limit)
			{
				// make date object
				$expires_at = Date::make()->remake('-'.$age_limit);

				// catch date error...
				if (!$expires_at->time()) throw new \Exception('Invalid age limit.');

				// if too old...
				if ($check->created_at < $expires_at->format('%F %X'))
				{
					// delete
					static::delete($hash, $endpoint);

					// return new lookup
					return static::run($name, $closure, $hash, $endpoint);
				}
			}

			// load result (suppress error)
			$result = json_decode($check->response);

			// if false (something went wrong)
			if (!$result)
			{
				// delete
				static::delete($hash, $endpoint);

				// rerun
				return static::run($name, $closure, $hash, $endpoint);
			}

			// return
			return $result;
		}

		// else if NOT found...
		else
		{
			// return lookup
			return static::run($name, $closure, $hash, $endpoint);
		}
	}

	/**
	 * Return the response of an actual API query.
	 *
	 * @param	string	$name
	 * @param	closure	$closure
	 * @param	string	$hash
	 * @param	string	$endpoint
	 * @return	array
	 */
	protected static function run($name, $closure, $hash, $endpoint)
	{
		// run closure
		$response = $closure();

		// save record
		static::set($name, $hash, $response, $endpoint);

		// return
		return $response;
	}

	/**
	 * Return a serialized closure.
	 *
	 * @param	closure	$closure
	 * @param	string	$id
	 * @return	string
	 */
	protected static function hash($name, $closure, $id)
	{
		// if custom id, return that
		if ($id) return md5(strtoupper($name.$id));

		// serializer
        $serializer = new Serializer();

        // serialize
        $string = $serializer->serialize($closure);

        // return
        return md5($string);
	}

	/**
	 * Get the stored response.
	 *
	 * @param	string	$hash
	 * @param	string	$endpoint
	 * @return	object
	 */
	public static function get($hash, $endpoint = null)
	{
		// if using remote api...
		if ($endpoint)
		{
			$check = API::get($hash, $endpoint);
		}

		// else if using database...
		else
		{
			$check = Model::where('hash', '=', $hash)->first();
		}

		// return
		return $check;
	}

	/**
	 * Delete a stored response.
	 *
	 * @param	string	$hash
	 * @param	string	$endpoint
	 * @return	void
	 */
	public static function delete($hash, $endpoint = null)
	{
		// if using remote api...
		if ($endpoint)
		{
			API::delete($hash, $endpoint);
		}

		// else if using database...
		else
		{
			Model::where('hash', '=', $hash)->delete();
		}
	}

	/**
	 * Save a calculated response.
	 *
	 * @param	string	$name
	 * @param	string	$hash
	 * @param	mixed	$response
	 * @param	string	$endpoint
	 * @return	object
	 */
	public static function set($name, $hash, $response, $endpoint = null)
	{
		// if using remote api...
		if ($endpoint)
		{
			API::set($name, $hash, $response, $endpoint);
		}

		// else if using database...
		else
		{
			Model::create([
				'name' => $name,
				'hash' => $hash,
				'response' => json_encode($response),
			]);
		}
	}
}