<?php

	class TestTimberPostType extends Timber_UnitTestCase {

		function testPostTypeObject() {
			$obj = get_post_type_object('post');
			$this->assertEquals('Posts', $obj->labels->name);
		}

		function testPostTypeProperty(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$this->assertEquals('post', $post->post_type);
		}

		/**
		 * @ticket #2111
		 */
		function testNonExistentPostType() {
			$post_type = new Timber\PostType('foobar');
			$this->assertEquals('foobar', $post_type->slug);
			$this->assertEquals('Timber\PostType', get_class($post_type));
		}

		function testPostTypeMethodInTwig() {
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$template = '{{post.post_type}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('post', $str);
		}

		function testTypeMethodInTwig() {
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$template = '{{post.type}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('post', $str);
		}

		function testTypeMethodInTwigLabels() {
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$template = '{{post.type.labels.name}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Posts', $str);
		}

		function testLegacyTypeCustomField() {
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'type', 'numberwang');
			$post = new TimberPost($post_id);
			$template = '{{post.type}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('numberwang', $str);
		}

		function testUnderscoreTypeCustomField() {
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, '_type', 'numberwang');
			$post = new TimberPost($post_id);
			$template = '{{post._type}}';
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('numberwang', $str);
		}

	}
