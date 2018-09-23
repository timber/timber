<?php

namespace Timber\Factory;

/**
 * Class PostFactory
 * @package Timber
 */
class PostFactory extends Factory implements FactoryInterface {

	/**
	 * @param \WP_Post|int|string|null $post_identifier
	 *
	 * @return \Timber\Post|null
	 */
	public static function get( $post_identifier = null ) {
		static $self = null;
		if ($self === null) {
			$self = new self;
		}
		return $self->get_object( $post_identifier );
	}

	/**
	 * @param \WP_Post|int|null $post_identifier
	 *
	 * @return \Timber\Post|null
	 */
	public function get_object( $post_identifier = null ) {
		$post = PostGetter::get_object( $post_identifier );

		$class = ObjectClassFactory::get_class( 'post', get_post_type( $post ), $post, $this->object_class );

		return $post ? new $class( $post ) : null;
	}
}
