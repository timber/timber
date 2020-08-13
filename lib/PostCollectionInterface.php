<?php

namespace Timber;

use ArrayAccess;
use Countable;
use Traversable;

/**
 * PostArrayObject class for dealing with arbitrary collections of Posts
 * (typically not from a WP_Query)
 *
 * @api
 */
interface PostCollectionInterface extends Traversable, Countable, ArrayAccess {
  public function pagination(array $options = []);
}