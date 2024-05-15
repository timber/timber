<?php

namespace Timber;

use AllowDynamicProperties;
use Stringable;

/**
 * Wrapper for the post_type object provided by WordPress
 * @since 1.0.4
*/
#[AllowDynamicProperties]
class PostType implements Stringable
{
    /**
     * @param string $slug
     */
    public function __construct(
        private $slug
    ) {
        $this->init($this->slug);
    }

    public function __toString()
    {
        return $this->slug;
    }

    protected function init($post_type)
    {
        $obj = \get_post_type_object($post_type);
        if (!empty($obj)) {
            foreach (\get_object_vars($obj) as $key => $value) {
                if ($key === '' || \ord($key[0]) === 0) {
                    continue;
                }
                $this->$key = $value;
            }
        }
    }
}
