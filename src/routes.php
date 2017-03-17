<?php

use Travis\Catalog\API;

Route::post('librarian/{method}', function($method)
{
	// return
	return API::receive($method, Request::input());
});