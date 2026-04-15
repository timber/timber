<?php

namespace Timber;

use Twig\TemplateWrapper;

/**
 * A decorator for Loader that renders specific Twig blocks from templates.
 *
 * This class extends Loader to provide block-specific rendering functionality.
 */
class TwigBlockLoader extends Loader
{
    /**
     * Constructor.
     *
     * @param string|array|null $caller  The caller information.
     * @param string             $block_name The name of the block to render.
     */
    public function __construct(
        $caller,
        protected $block_name
    ) {
        parent::__construct($caller);
    }

    /**
     * Generate a cache key for the template that includes the block name.
     *
     * @param string $file The template file.
     * @param array  $data The data to pass to the template.
     * @return string|null The cache key or null if encoding failed.
     */
    protected function get_cache_key($file, $data)
    {
        \ksort($data);
        $encoded = \json_encode($data);

        if (false === $encoded) {
            return null;
        }

        // Include block name in cache key to differentiate from full template renders
        return \md5($file . $encoded . $this->block_name);
    }

    /**
     * Render a Twig template block.
     *
     * Overrides the parent method to render a specific block instead of the entire template.
     *
     * @param TemplateWrapper $template The Twig template.
     * @param array                  $data     The data to pass to the template.
     * @return string The rendered output.
     */
    protected function render_twig_template($template, $data)
    {
        // Render the specific block if it exists, otherwise render the entire template
        if ($this->block_name && $template->hasBlock($this->block_name)) {
            return $template->renderBlock($this->block_name, $data);
        }

        return $template->render($data);
    }
}
