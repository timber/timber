<?php

namespace Timber;


interface CallerCompatibleLoaderInterface
{
	public function setCaller($caller = false);

	public function resetCaller();
}
