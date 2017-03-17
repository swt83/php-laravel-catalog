<?php

use Travis\Catalog\API;

Route::post('librarian/{method}', function($method)
{
	// decode
	$input = json_decode(Request::input('input'));

	// return
	return API::receive($method, $input);
});