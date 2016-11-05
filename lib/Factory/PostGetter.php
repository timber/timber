<?php

namespace Timber\Factory;

/**
 * Class Post
 * @package Timber\Factory
 */
class PostGetter implements ObjectGetterInterface {

	/**
	 * @param null $identifier
	 *
	 * @return \WP_Post|null
	 */
	static function get_object( $identifier = null ) {
		$identifier = static::determine_id( $identifier );

		return $identifier ? static::get_post_from_identifier( $identifier ) : null;
	}

	/**
	 * @param $identifier
	 *
	 * @return false|int
	 */
	static function determine_id( $identifier ) {
		if ( null === $identifier ) {
			return static::determine_id_from_query();
		}

		return $identifier;
	}

	/**
	 * Grabs the global post, from:
	 * \WP_Query::queried_object_id
	 *
	 *
	 *
	 * @return false|int
	 */
	static function determine_id_from_query() {
		global $wp_query;
		$identifier = null;

		if ( isset( $wp_query->queried_object_id )
		     && $wp_query->queried_object_id
		     && isset( $wp_query->queried_object )
		     && is_object( $wp_query->queried_object )
		     && get_class( $wp_query->queried_object ) == 'WP_Post'
		) {
			if ( isset( $_GET[ 'preview' ] ) && isset( $_GET[ 'preview_nonce' ] ) && wp_verify_nonce( $_GET[ 'preview_nonce' ], 'post_preview_' . $wp_query->queried_object_id ) ) {
				$identifier = static::get_post_preview_id( $wp_query );
			} else if ( ! $identifier ) {
				$identifier = $wp_query->queried_object_id;
			}
		} else if ( $wp_query->is_home && isset( $wp_query->queried_object_id ) && $wp_query->queried_object_id ) {
			//hack for static page as home page
			$identifier = $wp_query->queried_object_id;
		} else {
			$identifier = get_the_ID();
			if ( ! $identifier ) {
				global $wp_query;
				if ( isset( $wp_query->query[ 'p' ] ) ) {
					$identifier = $wp_query->query[ 'p' ];
				}
			}
		}

		if ( $identifier === null && ( $identifier_from_loop = PostGetter::loop_to_id() ) ) {
			$identifier = $identifier_from_loop;
		}

		return $identifier;
	}

	/**
	 * @param $query
	 *
	 * @return bool|void
	 */
	protected static function get_post_preview_id( $query ) {
		$can = array(
			'edit_' . $query->queried_object->post_type . 's',
		);

		if ( $query->queried_object->author_id !== get_current_user_id() ) {
			$can[] = 'edit_others_' . $query->queried_object->post_type . 's';
		}

		$can_preview = array();

		foreach ( $can as $type ) {
			if ( current_user_can( $type ) ) {
				$can_preview[] = true;
			}
		}

		if ( count( $can_preview ) !== count( $can ) ) {
			return false;
		}

		$revisions = wp_get_post_revisions( $query->queried_object_id );

		if ( ! empty( $revisions ) ) {
			$revision = reset( $revisions );

			return $revision->ID;
		}

		return false;
	}

	/**
	 * takes a mix of integer (post ID), string (post slug),
	 * or object to return a WordPress post object from WP's built-in get_post() function
	 * @internal
	 *
	 * @param integer|string|object|\WP_Post $identifier
	 *
	 * @return \WP_Post|null
	 */
	protected static function get_post_from_identifier( $identifier = 0 ) {
		if ( is_string( $identifier ) || is_numeric( $identifier ) || ( is_object( $identifier ) && ! isset( $identifier->post_title ) ) || $identifier === 0 ) {
			$pid  = self::check_post_id( $identifier );
			$post = get_post( $pid );
			return $post;
		}

		//we can skip if already is WP_Post
		return $identifier;
	}

	/**
	 * helps you find the post id regardless of whether you send a string or whatever
	 *
	 * @param integer $pid ;
	 *
	 * @internal
	 * @return integer|null ID number of a post
	 */
	protected static function check_post_id( $pid ) {
		if ( is_numeric( $pid ) && $pid === 0 ) {
			$pid = get_the_ID();

			return $pid;
		}
		if ( ! is_numeric( $pid ) && is_string( $pid ) ) {
			$pid = self::get_post_id_by_name( $pid );

			return $pid;
		}
		if ( ! $pid ) {
			return null;
		}

		return $pid;
	}

	/**
	 * get_post_id_by_name($post_name)
	 * @internal
	 *
	 * @param string $post_name
	 *
	 * @return int
	 */
	public static function get_post_id_by_name( $post_name ) {
		global $wpdb;
		$query  = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s LIMIT 1", $post_name );
		$result = $wpdb->get_row( $query );
		if ( ! $result ) {
			return null;
		}

		return $result->ID;
	}


}