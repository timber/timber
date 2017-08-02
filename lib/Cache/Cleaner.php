<?php

namespace Timber\Cache;

use Timber\Cache;

class Cleaner {

	public static function delete_transients( )
	{
		Cache::delete_transients();
	}


}