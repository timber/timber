<?php

class WP_UnitTest_Factory {
	function __construct() {
		$this->post = new WP_UnitTest_Factory_For_Post( $this );
		$this->comment = new WP_UnitTest_Factory_For_Comment( $this );
		$this->user = new WP_UnitTest_Factory_For_User( $this );
	}
}

class WP_UnitTest_Factory_For_Post extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'post_status' => 'publish',
			'post_title' => new WP_UnitTest_Generator_Sequence( 'Post title %s' ),
			'post_content' => new WP_UnitTest_Generator_Sequence( 'Post content %s' ),
			'post_excerpt' => new WP_UnitTest_Generator_Sequence( 'Post excerpt %s' ),
			'post_type' => 'post'
		);
	}

	function create_object( $args ) {
		return wp_insert_post( $args );
	}

	function update_object( $post_id, $fields ) {
		$fields['ID'] = $post_id;
		return wp_update_post( $fields );
	}
}

class WP_UnitTest_Factory_For_User extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'user_login' => new WP_UnitTest_Generator_Sequence( 'User %s' ),
			'user_pass' => 'a',
			'user_email' => new WP_UnitTest_Generator_Sequence( 'user_%s@example.org' ),
		);
	}

	function create_object( $args ) {
		return wp_insert_user( $args );
	}

	function update_object( $user_id, $fields ) {
		$fields['ID'] = $user_id;
		return wp_update_user( $fields );
	}
}

class WP_UnitTest_Factory_For_Comment extends WP_UnitTest_Factory_For_Thing {

	function __construct( $factory = null ) {
		parent::__construct( $factory );
		$this->default_generation_definitions = array(
			'comment_author' => new WP_UnitTest_Generator_Sequence( 'Commenter %s' ),
			'comment_author_url' => new WP_UnitTest_Generator_Sequence( 'http://example.com/%s/' ),
			'comment_approved' => 1,
		);
	}

	function create_object( $args ) {
		return wp_insert_comment( $args );
	}

	function update_object( $comment_id, $fields ) {
		$fields['comment_ID'] = $comment_id;
		return wp_update_comment( $fields );
	}

	function create_post_comments( $post_id, $count = 1, $args = array(), $generation_definitions = null ) {
		$args['comment_post_ID'] = $post_id;
		return $this->create_many( $count, $args, $generation_definitions );
	}
}


abstract class WP_UnitTest_Factory_For_Thing {

	var $default_generation_definitions;
	var $factory;

	/**
	 * Creates a new factory, which will create objects of a specific Thing
	 *
	 * @param object $factory Global factory that can be used to create other objects on the system
	 * @param array $default_generation_definitions Defines what default values should the properties of the object have. The default values
	 * can be generators -- an object with next() method. There are some default generators: {@link WP_UnitTest_Generator_Sequence},
	 * {@link WP_UnitTest_Generator_Locale_Name}, {@link WP_UnitTest_Factory_Callback_After_Create}.
	 */
	function __construct( $factory, $default_generation_definitions = array() ) {
		$this->factory = $factory;
		$this->default_generation_definitions = $default_generation_definitions;
	}

	abstract function create_object( $args );
	abstract function update_object( $object, $fields );

	function create( $args = array(), $generation_definitions = null ) {
		if ( is_null( $generation_definitions ) )
			$generation_definitions = $this->default_generation_definitions;

		$generated_args = $this->generate_args( $args, $generation_definitions, $callbacks );
		$created = $this->create_object( $generated_args );
		if ( !$created || is_wp_error( $created ) )
			return $created;

		if ( $callbacks ) {
			$updated_fields = $this->apply_callbacks( $callbacks, $created );
			$save_result = $this->update_object( $created, $updated_fields );
			if ( !$save_result || is_wp_error( $save_result ) )
				return $save_result;
		}
		return $created;
	}

	function create_many( $count, $args = array(), $generation_definitions = null ) {
		$results = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$results[] = $this->create( $args, $generation_definitions );
		}
		return $results;
	}

	function generate_args( $args = array(), $generation_definitions = null, &$callbacks = null ) {
		$callbacks = array();
		if ( is_null( $generation_definitions ) )
			$generation_definitions = $this->default_generation_definitions;

		foreach( array_keys( $generation_definitions ) as $field_name ) {
			if ( !isset( $args[$field_name] ) ) {
				$generator = $generation_definitions[$field_name];
				if ( is_scalar( $generator ) )
					$args[$field_name] = $generator;
				elseif ( is_object( $generator ) && method_exists( $generator, 'call' ) ) {
					$callbacks[$field_name] = $generator;
				} elseif ( is_object( $generator ) )
					$args[$field_name] = $generator->next();
				else
					return new WP_Error( 'invalid_argument', 'Factory default value should be either a scalar or an generator object.' );
			}
		}
		return $args;
	}

	function apply_callbacks( $callbacks, $created ) {
		$updated_fields = array();
		foreach( $callbacks as $field_name => $generator ) {
			$updated_fields[$field_name] = $generator->call( $created );
		}
		return $updated_fields;
	}

	function callback( $function ) {
		return new WP_UnitTest_Factory_Callback_After_Create( $function );
	}
}

class WP_UnitTest_Generator_Sequence {
	var $next;
	var $template_string;

	function __construct( $template_string = '%s', $start = 1 ) {
		$this->next = $start;
		$this->template_string = $template_string;
	}

	function next() {
		$generated = sprintf( $this->template_string , $this->next );
		$this->next++;
		return $generated;
	}
}

class WP_UnitTest_Factory_Callback_After_Create {
	var $callback;

	function __construct( $callback ) {
		$this->callback = $callback;
	}

	function call( $object ) {
		return call_user_func( $this->callback, $object );
	}
}
