<?php

namespace Travis;

use SuperClosure\Serializer;
use Travis\Catalog\Model;
use Travis\Date;

class Catalog
{
	/**
	 * Return a cached API response, or a new one.
	 *
	 * @param	string	$name
	 * @param	closure	$closure
	 * @param	string	$age_limit
	 * @return	array
	 */
	public static function lookup($name, $closure, $age_limit = null)
	{
		// calculate hash
		$hash = static::hash($closure);

		// check database
		$check = Model::where('hash', '=', $hash)
			->first();

		// if found...
		if ($check)
		{
			// if age limit...
			if ($age_limit)
			{
				// make date object
				$expires_at = Date::make()->remake('-'.$age_limit);

				// catch date error...
				if (!$expires_at->time()) trigger_error('Invalid age limit.');

				// if too old...
				if ($check->created_at < $expires_at->format('%F %X'))
				{
					// delete
					Model::where('hash', '=', $hash)->delete();

					// return new lookup
					return static::run($name, $closure);
				}
			}

			// load result (suppress error)
			$result = json_decode($check->response);

			// if false (something went wrong)
			if (!$result)
			{
				// delete
				Model::where('hash', '=', $hash)->delete();

				// rerun
				return static::run($name, $closure);
			}

			// return
			return $result;
		}

		// else if NOT found...
		else
		{
			// return lookup
			return static::run($name, $closure);
		}
	}

	/**
	 * Return the response of an actual API query.
	 *
	 * @param	string	$name
	 * @param	closure	$closure
	 * @return	array
	 */
	protected static function run($name, $closure)
	{
		// run closure
		$response = $closure();

		// calculate hash
		$hash = static::hash($closure);

		// save record
		Model::create([
			'hash' => $hash,
			'name' => $name,
			'response' => json_encode($response),
		]);

		// return
		return $response;
	}

	/**
	 * Return a serialized closure.
	 *
	 * @param	closure	$closure
	 * @return	string
	 */
	protected static function hash($closure)
	{
		// serializer
        $serializer = new Serializer();

        // serialize
        $string = $serializer->serialize($closure);

        // return
        return md5($string);
	}
}