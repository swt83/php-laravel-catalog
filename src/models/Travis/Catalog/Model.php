<?php

namespace Travis\Catalog;

class Model extends \Eloquent
{
	public $connection = 'catalog';
	public $table = 'catalog';
    public $timestamps = true;
    public $guarded = [];
}