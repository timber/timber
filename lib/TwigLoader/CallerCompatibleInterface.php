<?php

namespace Timber\TwigLoader;

/**
 * @author Heino H. Gehlsen <heino@gehlsen.dk>
 * @copyright 2017 Heino H. Gehlsen
 * @license MIT
 */
interface CallerCompatibleInterface
{
	public function setCaller($caller = false);

	public function resetCaller();
}
