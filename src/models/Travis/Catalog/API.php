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
		// init
		$is_success = true;
		$message = null;
		$data = null;

		// attempt to process...
		try
		{
			// switch
			switch ($method)
			{
				case 'get':
					$data = Catalog::get($input->hash);
					if (!$data)
					{
						$is_success = false;
						$message = 'Record not found.';
					}
					break;
				case 'unset':
					Catalog::unset($input->hash);
					break;
				case 'set':
					Catalog::set($input->name, $input->hash, $input->response);
					break;
				default:
					$is_success = false;
					$message = 'Invalid request.';
					break;
			}
		}

		// if an error was found...
		catch (\Exception $e)
		{
			$is_success = false;
			$message = $e->getMessage();
		}

		// return
		return json_encode(['is_success' => $is_success, 'message' => $message, 'data' => $data]);
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
		// request
		$response = static::request($endpoint, 'get', ['hash' => $hash]);

		// return
		return $response->is_success ? $response->data : null;
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
		static::request($endpoint, 'unset', ['hash' => $hash]);
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
	public static function set($name, $hash, $response, $endpoint)
	{
		static::request($endpoint, 'set', ['name' => $name, 'hash' => $hash, 'response' => $response]);
	}

	/**
	 * Submit the request.
	 *
	 * @param	string	$endpoint
	 * @param	string	$method
	 * @param	array	$input
	 * @return	mixed
	 */
	protected static function request($endpoint, $method, $input)
	{
		// make url
		$endpoint = $endpoint.'/'.$method;

		// make request
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['input' => json_encode($input)]);
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