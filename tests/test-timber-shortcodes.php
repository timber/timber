<?php

	class TestTimberShortcodes extends Timber_UnitTestCase {

		function testShortcodes(){
			add_shortcode('timber_shortcode', function($text){
				return 'timber '.$text[0];
			});
			$return = Timber::compile('assets/test-shortcodes.twig');
			$this->assertEquals('hello timber foo', trim($return));
		}

		/**
		 * @ticket #2268
		 */
		function testCustomFieldShortcode() {
			add_shortcode( 'foobar', function( $atts ) {
				return 'barfoo';
			} );

			$post_id = $this->factory->post->create();
			update_post_meta( $post_id, 'customfield', '[foobar]' );
			$template = '{{ post.customfield | shortcodes }}';

			$post = Timber::get_post($post_id);
			$compiled = Timber::compile_string($template, ['post' => $post]);

			$this->assertEquals('barfoo', $compiled);
		}
	}
