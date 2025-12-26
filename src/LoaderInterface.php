<?php

namespace Timber;

/**
 * Interface for Timber template loaders.
 */
interface LoaderInterface
{
    /**
     * Render a template file.
     *
     * @param string            $file       The template file to render.
     * @param array             $data       Data to pass to the template.
     * @param array|boolean     $expires    Cache expiration (array for options, false for none, integer for # of seconds).
     * @param string            $cache_mode Cache mode constant.
     * @return bool|string                  The rendered output or false on failure.
     */
    public function render($file, $data = null, $expires = false, $cache_mode = Loader::CACHE_USE_DEFAULT);

    /**
     * Get first existing template.
     *
     * @param array|string $templates  Name(s) of the Twig template(s) to choose from.
     * @return string|bool             Name of chosen template, otherwise false.
     */
    public function choose_template($templates);
}
