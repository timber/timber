<?php

	class TestTimberRevisions extends Timber_UnitTestCase {

		function testPreviewClass() {
			global $current_user;
			global $wp_query;

			$quote = 'The way to do well is to do well.';
			$post_id = $this->factory->post->create(array(
				'post_content' => $quote,
				'post_author' => 5
			));
			$revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
				'post_content' => $quote . 'Yes'
			));

			$uid = $this->factory->user->create(array(
				'user_login' => 'timber',
				'user_pass' => 'timber',
			));

			$original_post = new Timber\Post($post_id);
			$user = wp_set_current_user($uid);

			$user->add_role('administrator');
			$wp_query->queried_object_id = $post_id;
			$wp_query->queried_object = get_post($post_id);
			$_GET['preview'] = true;
			$_GET['preview_nonce'] = wp_create_nonce('post_preview_' . $post_id);
			$post = new TimberPost();
			$this->assertEquals( $original_post->class(), $post->class() );
		}

		function testPreviewContent(){
			global $current_user;
			global $wp_query;

			$quote = 'The way to do well is to do well.';
			$post_id = $this->factory->post->create(array(
				'post_content' => $quote,
				'post_author' => 5
			));
			$revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
				'post_content' => $quote . 'Yes'
			));

			$uid = $this->factory->user->create(array(
				'user_login' => 'timber',
				'user_pass' => 'timber',
			));
			$user = wp_set_current_user($uid);

			$user->add_role('administrator');
			$wp_query->queried_object_id = $post_id;
			$wp_query->queried_object = get_post($post_id);
			$_GET['preview'] = true;
			$_GET['preview_nonce'] = wp_create_nonce('post_preview_' . $post_id);
			$post = new TimberPost();
			$this->assertEquals( $quote . 'Yes', $post->post_content );
		}

		function testMultiPreviewRevisions(){
			global $current_user;
			global $wp_query;

			$quote = 'The way to do well is to do well.';
			$post_id = $this->factory->post->create(array(
				'post_content' => $quote,
				'post_author' => 5
			));
			$old_revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
				'post_content' => $quote . 'Yes'
			));

			$revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
				'post_content' => 'I am the one'
			));

			$uid = $this->factory->user->create(array(
				'user_login' => 'timber',
				'user_pass' => 'timber',
			));
			$user = wp_set_current_user($uid);

			$user->add_role('administrator');
			$wp_query->queried_object_id = $post_id;
			$wp_query->queried_object = get_post($post_id);
			$_GET['preview'] = true;
			$_GET['preview_nonce'] = wp_create_nonce('post_preview_' . $post_id);
			$post = new TimberPost();
			$this->assertEquals('I am the one', $post->post_content);
		}

		function testCustomFieldPreviewRevision(){
			global $current_user;
			global $wp_query;

			$post_id = $this->factory->post->create(array(
				'post_author' => 5,
			));
			update_field('test_field', 'The custom field content', $post_id);

			$assertCustomFieldVal = 'This has been revised';
			$revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
			));
			update_field('test_field', $assertCustomFieldVal, $revision_id);

			$uid = $this->factory->user->create(array(
				'user_login' => 'timber',
				'user_pass' => 'timber',
			));
			$user = wp_set_current_user($uid);
			$user->add_role('administrator');

			$wp_query->queried_object_id = $post_id;
			$wp_query->queried_object = get_post($post_id);
			$_GET['preview'] = true;
			$_GET['preview_nonce'] = wp_create_nonce('post_preview_' . $post_id);
			$post = new TimberPost($post_id);

			$str_direct = Timber::compile_string('{{post.test_field}}', array('post' => $post));
			$str_getfield = Timber::compile_string('{{post.get_field(\'test_field\')}}', array('post' => $post));

			$this->assertEquals( $assertCustomFieldVal, $str_direct );
			$this->assertEquals( $assertCustomFieldVal, $str_getfield );
		}

		function testCustomFieldPreviewNotRevision() {
			global $current_user;
			global $wp_query;
			$original_content = 'The custom field content';

			$post_id = $this->factory->post->create(array(
				'post_author' => 5,
			));
			update_field('test_field', $original_content, $post_id);

			$assertCustomFieldVal = 'This has been revised';
			$revision_id = $this->factory->post->create(array(
				'post_type' => 'revision',
				'post_status' => 'inherit',
				'post_parent' => $post_id,
			));
			update_field('test_field', $assertCustomFieldVal, $revision_id);

			$uid = $this->factory->user->create(array(
				'user_login' => 'timber',
				'user_pass' => 'timber',
			));
			$user = wp_set_current_user($uid);
			$user->add_role('administrator');

			$wp_query->queried_object_id = $post_id;
			$wp_query->queried_object = get_post($post_id);
			$post = new TimberPost($post_id);

			$str_direct = Timber::compile_string('{{post.test_field}}', array('post' => $post));
			$str_getfield = Timber::compile_string('{{post.get_field(\'test_field\')}}', array('post' => $post));

			$this->assertEquals( $original_content, $str_direct );
			$this->assertEquals( $original_content, $str_getfield );
		}
}