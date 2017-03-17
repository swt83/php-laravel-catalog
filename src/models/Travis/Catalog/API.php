<?php

namespace Travis\Catalog;

use Travis\Catalog;

class API
{
	/**
	 * Process the request.
	 *
	 * @param	string	$method
	 * @param	array 	$input
	 * @return	object
	 */
	public static function receive($method, $input)
	{
		// flag
		$is_success = true;

		// init
		$data = null;

		// attempt to process...
		try
		{
			// switch
			switch ($method)
			{
				case 'get':
					Catalog::get($input['hash']);
					break;
				case 'unset':
					Catalog::unset($input['hash']);
					break;
				case 'set':
					Catalog::get($input['name'], $input['hash'], $input['response']);
					break;
				default:
					$is_success = false;
					$data = 'Invalid request.';
					break;
			}
		}

		// if an error was found...
		catch (\Exception $e)
		{
			$is_success = false;
			$data = $e->getMessage();
		}

		// return
		return request(json_encode(['is_success' => $is_error, 'data' => $data]), $is_success ? 200 : 422);
	}

	/**
	 * Get the stored response.
	 *
	 * @param	string	$hash
	 * @param	string	$endpoint
	 * @return	object
	 */
	public static function get($hash, $endpoint)
	{
		return static::send($endpoint, 'get', ['hash' => $hash]);
	}

	/**
	 * Delete a stored response.
	 *
	 * @param	string	$hash
	 * @param	string	$endpoint
	 * @return	void
	 */
	public static function unset($hash, $endpoint)
	{
		return static::send($endpoint, 'unset', ['hash' => $hash]);
	}

	/**
	 * Save a calculated response.
	 *
	 * @param	string	$hash
	 * @param	mixed	$response
	 * @param	string	$endpoint
	 * @return	object
	 */
	public static function set($hash, $response, $endpoint)
	{
		return static::send($endpoint, 'set', ['hash' => $hash, 'response' => $response]);
	}

	/**
	 * Submit the request.
	 *
	 * @param	string	$endpoint
	 * @param	string	$method
	 * @param	array	$input
	 * @return	mixed
	 */
	protected static function send($endpoint, $method, $input)
	{
		// make url
		$endpoint = $endpoint.'/'.$method;

		// make payload
		$payload = json_encode($input);

		// make request
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $response = curl_exec($ch);

        // catch error...
        if (curl_errno($ch))
        {
            // report
            #$errors = curl_error($ch);

            // close
            curl_close($ch);

            // return false
            throw new \Exception('Unable to connect to API.');
        }

        // close
        curl_close($ch);

        // decode
        $response = json_decode($response);

        // catch error...
        if (!$response) throw new \Exception('Unable to read API response.');

        // return
        return $response;
	}
}