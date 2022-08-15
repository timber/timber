<?php

namespace Timber;

/**
 * Class Request
 *
 * Timber\Request exposes $_GET and $_POST to the context.
 */
class Request
{
    public $post = [];

    public $get = [];

    /**
     * Constructs a Timber\Request object
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @internal
     */
    protected function init()
    {
        $this->post = $_POST;
        $this->get = $_GET;
    }
}
