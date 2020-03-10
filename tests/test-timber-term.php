<?php

	/**
	* @group terms-api
	*/
	class TestTimberTerm extends Timber_UnitTestCase {

		function testTermFrom() {
			register_taxonomy('baseball', array('post'));
			register_taxonomy('hockey', array('post'));
			$this->factory->term->create(['name' => 'Rangers',   'taxonomy' => 'baseball']);
			$this->factory->term->create(['name' => 'Cardinals', 'taxonomy' => 'baseball']);
			$this->factory->term->create(['name' => 'Rangers',   'taxonomy' => 'hockey']);

			$wp_terms       = get_terms([
				'taxonomy'    => 'baseball',
				'hide_empty'  => false,
			]);
			$baseball_teams = Timber::get_terms($wp_terms);

			$this->assertCount(2, $baseball_teams);

			$this->assertEquals('Cardinals', $baseball_teams[0]->title());
			$this->assertEquals('Rangers',   $baseball_teams[1]->title());
		}

		/**
		 * @expectedException InvalidArgumentException
		 */
		function testTermFromInvalidObject() {
			register_taxonomy('baseball', array('post'));
			$term_id = $this->factory->term->create(['name' => 'Cardinals', 'taxonomy' => 'baseball']);
			$post_id = $this->factory->post->create(['post_title' => 'Test Post']);
			$post = get_post($post_id);
			$test = Timber::get_terms($post);
		}

		function testGetTerm() {
			register_taxonomy('arts', array('post'));

			$term_id = $this->factory->term->create(array('name' => 'Zong', 'taxonomy' => 'arts'));
			// @todo #2087 get this to work w/o $taxonomy param
			$term = Timber::get_term($term_id, '');
			$this->assertEquals('Zong', $term->title());
			$template = '{% set zp_term = Term("'.$term->ID.'", "arts") %}{{ zp_term.name }}';
			$string = Timber::compile_string($template);
			$this->assertEquals('Zong', $string);
		}

		function testTerm() {
			$term_id = $this->factory->term->create();
			$term = Timber::get_term($term_id);
			$this->assertEquals('Timber\Term', get_class($term));
		}

		function testGetTermWithObject() {
			$term_id = $this->factory->term->create(array('name' => 'Famous Commissioners'));
			$term_data = get_term($term_id, 'post_tag');
			$this->assertTrue( in_array( get_class($term_data), array('WP_Term', 'stdClass') ) );
			$term = Timber::get_term($term_id);
			$this->assertEquals('Famous Commissioners', $term->title());
			$this->assertEquals('Timber\Term', get_class($term));
		}

		function testTermToString() {
			$term_id = $this->factory->term->create(array('name' => 'New England Patriots'));
			$term = Timber::get_term($term_id);
			$str = Timber::compile_string('{{term}}', array('term' => $term));
			$this->assertEquals('New England Patriots', $str);
		}

		function testTermDescription() {
			$desc = 'An honest football team';
			$term_id = $this->factory->term->create(array('name' => 'New England Patriots', 'description' => $desc));
			$term = Timber::get_term($term_id, 'post_tag');
			$this->assertEquals($desc, $term->description());
		}

		function testTermInitObject() {
			$term_id = $this->factory->term->create();
			$term = get_term($term_id, 'post_tag');
			$term = Timber::get_term($term);
			$this->assertEquals($term->ID, $term_id);
		}

		function testTermLink() {
			$term_id = $this->factory->term->create();
			$term = Timber::get_term($term_id);
			$this->assertContains('http://', $term->link());
		}

		function testTermPath() {
			$term_id = $this->factory->term->create();
			$term = Timber::get_term($term_id);
			$this->assertFalse(strstr($term->path(), 'http://'));
		}

		function testGetPostsWithPostTypesString() {
			register_post_type('portfolio', array('taxonomies' => array('post_tag'), 'public' => true));
			$term_id = $this->factory->term->create(array('name' => 'Zong'));
			$posts = $this->factory->post->create_many(3, array('post_type' => 'post', 'tags_input' => 'zong') );
			$posts = $this->factory->post->create_many(5, array('post_type' => 'portfolio', 'tags_input' => 'zong') );
			$term = Timber::get_term($term_id);
			$posts_gotten = $term->posts('posts_per_page=4');
			$this->assertEquals(4, count($posts_gotten));

			$posts_gotten = $term->posts(array('posts_per_page' => 7));
			$this->assertEquals(7, count($posts_gotten));
		}

		function testPosts() {
			register_post_type('portfolio', array('taxonomies' => array('arts'), 'public' => true));
			register_taxonomy('arts', array('portfolio'));

			// create a term, and some posts to assign it to
			$term_id = $this->factory->term->create(array('name' => 'Zong', 'taxonomy' => 'arts'));
			$posts = $this->factory->post->create_many(5, array('post_type' => 'portfolio' ));

			// assign the term to each of our new posts
			foreach($posts as $post_id) {
				wp_set_object_terms($post_id, $term_id, 'arts', true);
			}

			$term = Timber::get_term($term_id);

			$this->assertEquals(5, count($term->posts()));
		}

		/**
		 * @expectedDeprecated {{ term.get_posts }}
		 */
		function testGetPostsOld() {
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = Timber::get_term($term_id);
			$gotten_posts = $term->get_posts();
			$this->assertEquals(count($posts), count($gotten_posts));
		}

		function testGetPostsAsPageOld() {
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				set_post_type($post_id, 'page');
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = Timber::get_term($term_id);
			$gotten_posts = $term->posts(count($posts), 'page');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->posts(count($posts), 'any');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->posts(count($posts), 'post');
			$this->assertEquals(0, count($gotten_posts));
		}

		/**
		 * @expectedDeprecated {{ term.get_posts }}
		 */
		function testGetPostsNew() {
			require_once('php/timber-post-subclass.php');
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				set_post_type($post_id, 'page');
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = Timber::get_term($term_id);
			$gotten_posts = $term->get_posts('post_type=page');
			$this->assertEquals(count($posts), count($gotten_posts));

			$gotten_posts = $term->get_posts('post_type=page', 'TimberPostSubclass');
			$this->assertEquals(count($posts), count($gotten_posts));
			$this->assertInstanceOf( 'TimberPostSubclass', $gotten_posts[0] );

			$gotten_posts = $term->get_posts(array('post_type' => 'page'), 'TimberPostSubclass');
			$this->assertInstanceOf( 'TimberPostSubclass', $gotten_posts[0] );
			$this->assertEquals(count($posts), count($gotten_posts));
		}

		function testPostsWithCustomPostType() {
			$term_id = $this->factory->term->create();
			$posts   = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();

			foreach ( $posts as $post_id ) {
				set_post_type( $post_id, 'page' );
				wp_set_object_terms( $post_id, $term_id, 'post_tag', true );
			}

			$term = Timber::get_term( $term_id );

			$term_posts = $term->posts( [
				'posts_per_page' => 2,
				'orderby'        => 'menu_order',
			], 'page' );

			$this->assertEquals( 'Timber\Post', get_class( $term_posts[0] ) );
			$this->assertEquals( 'page', $term_posts[0]->post_type );
			$this->assertEquals( 2, count( $term_posts ) );
		}

		function testPostsWithCustomPostTypeAndCustomClass() {
			require_once 'php/timber-post-subclass.php';

			$term_id = $this->factory->term->create();
			$posts   = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();

			foreach ( $posts as $post_id ) {
				set_post_type( $post_id, 'page' );
				wp_set_object_terms( $post_id, $term_id, 'post_tag', true );
			}

			$term = Timber::get_term( $term_id );

			$term_posts = $term->posts( [
				'posts_per_page' => 2,
				'orderby'        => 'menu_order',
			], 'page', 'TimberPostSubclass' );

			$this->assertInstanceOf( 'TimberPostSubclass', $term_posts[0] );
			$this->assertEquals( 'page', $term_posts[0]->post_type );
			$this->assertEquals( 2, count( $term_posts ) );
		}

		/**
		 * This test uses the logic described in https://github.com/timber/timber/issues/799#issuecomment-192445207.
		 */
		function testPostsWithCustomPostTypePageAndCustomClass() {
			require_once 'php/timber-post-subclass.php';
			require_once 'php/timber-post-subclass-page.php';

			$term_id = $this->factory->term->create();
			$posts   = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();

			foreach ( $posts as $post_id ) {
				set_post_type( $post_id, 'page' );
				wp_set_object_terms( $post_id, $term_id, 'post_tag', true );
			}

			$term = Timber::get_term( $term_id );

			$term_posts = $term->posts( [
				'posts_per_page' => 2,
				'orderby'        => 'menu_order',
			], 'page', 'TimberPostSubclass' );

			$this->assertInstanceOf( 'page', $term_posts[0] );
			$this->assertEquals( 'page', $term_posts[0]->post_type );
			$this->assertEquals( 2, count( $term_posts ) );
		}

		function testTermChildren() {
			$parent_id = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
			$local = $this->factory->term->create(array('name' => 'Local', 'parent' => $parent_id, 'taxonomy' => 'category'));
			$int = $this->factory->term->create(array('name' => 'International', 'parent' => $parent_id, 'taxonomy' => 'category'));

			// @todo #2087 get this to work w/o $taxonomy param
			$term = Timber::get_term($parent_id, '');
			$children = $term->children();
			$this->assertEquals(2, count($children));
			$this->assertEquals('Local', $children[0]->name);
		}

		/**
		 * @ticket #824
		 */
		function testTermWithNativeMeta() {
			$tid = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
			add_term_meta($tid, 'foo', 'bar');
			// @todo #2087 get this to work w/o $taxonomy param
			$term = Timber::get_term($tid, '');
			$template = '{{term.foo}}';
			$compiled = Timber::compile_string($template, array('term' => $term));
			$this->assertEquals('bar', $compiled);
		}

		/**
		 * @ticket #824
		 */
		function testTermWithNativeMetaFalse() {
			$tid = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
			add_term_meta($tid, 'foo', false);
			// @todo #2087 get this to work w/o $taxonomy param
			$term = Timber::get_term($tid, '');
			$this->assertEquals('', $term->meta('foo'));
		}

		/**
		 * @ticket #824
		 */
		function testTermWithNativeMetaNotExisting() {
			$tid = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));

			add_term_meta($tid, 'bar', 'qux');;
			$wp_native_value = get_term_meta($tid, 'foo', true);
			$acf_native_value = get_field('foo', 'category_'.$tid);

			$valid_wp_native_value = get_term_meta($tid, 'bar', true);
			$valid_acf_native_value = get_field('bar', 'category_'.$tid);

			// @todo #2087 get this to work w/o $taxonomy param
			$term = Timber::get_term($tid, '');

			//test baseline "bar" data
			$this->assertEquals('qux', $valid_wp_native_value);
			$this->assertEquals('qux', $valid_acf_native_value);
			$this->assertEquals('qux', $term->bar);

			//test the one taht doesn't exist
			$this->assertEquals('string', gettype($wp_native_value));
			$this->assertEmpty($wp_native_value);
			$this->assertNull($acf_native_value);
			$this->assertNotTrue($term->meta('foo'));
		}

		function testTermEditLink() {
			wp_set_current_user(1);
			$tid = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
			// @todo #2087 get this to work w/o $taxonomy param
			$term = Timber::get_term($tid, '');
			$links = array();

			$links[] = 'http://example.org/wp-admin/term.php?taxonomy=category&tag_ID='.$tid.'&post_type=post';
			$links[] = 'http://example.org/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID='.$tid.'&post_type=post';
			$links[] = 'http://example.org/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID='.$tid;
			$links[] = 'http://example.org/wp-admin/term.php?taxonomy=category&term_id='.$tid.'&post_type=post';
			$this->assertContains($term->edit_link(), $links);
		}
	}

	class Arts extends Timber\Term {

		function foobar() {
			return 'Zebra';
		}

	}
