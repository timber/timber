<?php

namespace Timber;

/**
 * PostArrayObject class for dealing with arbitrary collections of Posts
 * (typically not from a WP_Query)
 *
 * @api
 */
class PostArrayObject extends \ArrayObject implements PostCollectionInterface {
  /**
   * @inheritdoc
   */
  public function pagination(array $options = []) {
    return null;
  }

  /**
   * @inheritdoc
   */
  public function to_array() : array {
    return $this->getArrayCopy();
  }
}