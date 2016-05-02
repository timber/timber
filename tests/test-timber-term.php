<?php

	class TestTimberTerm extends Timber_UnitTestCase {

		function testTerm() {
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertEquals('Timber\Term', get_class($term));
		}

		function testGetTermWithObject() {
			$term_id = $this->factory->term->create(array('name' => 'Famous Commissioners'));
			$term_data = get_term($term_id, 'post_tag');
			$this->assertTrue( in_array( get_class($term_data), array('WP_Term', 'stdClass') ) );
			$term = new TimberTerm($term_id);
			$this->assertEquals('Famous Commissioners', $term->name());
			$this->assertEquals('Timber\Term', get_class($term));
		}

		function testTermConstructWithSlug() {
			$term_id = $this->factory->term->create(array('name' => 'New England Patriots'));
			$term = new TimberTerm('new-england-patriots');
			$this->assertEquals($term->ID, $term_id);
		}

		function testTermToString() {
			$term_id = $this->factory->term->create(array('name' => 'New England Patriots'));
			$term = new TimberTerm('new-england-patriots');
			$str = Timber::compile_string('{{term}}', array('term' => $term));
			$this->assertEquals('New England Patriots', $str);
		}

		function testTermDescription() {
			$desc = 'An honest football team';
			$term_id = $this->factory->term->create(array('name' => 'New England Patriots', 'description' => $desc));
			$term = new TimberTerm($term_id, 'post_tag');
			$this->assertEquals($desc, $term->description());
		}

		function testTermConstructWithName() {
			$term_id = $this->factory->term->create(array('name' => 'St. Louis Cardinals'));
			$term = new TimberTerm('St. Louis Cardinals');
			$this->assertNull($term->ID);
		}

		function testTermInitObject() {
			$term_id = $this->factory->term->create();
			$term = get_term($term_id, 'post_tag');
			$term = new TimberTerm($term);
			$this->assertEquals($term->ID, $term_id);
		}

		function testTermLink() {
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertContains('http://', $term->link());
			$this->assertContains('http://', $term->get_link());
		}

		function testTermPath() {
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertFalse(strstr($term->path(), 'http://'));
			$this->assertFalse(strstr($term->get_path(), 'http://'));
		}

		function testGetPostsWithPostTypesString() {
			register_post_type('portfolio', array('taxonomies' => array('post_tag'), 'public' => true));
			$term_id = $this->factory->term->create(array('name' => 'Zong'));
			$posts = $this->factory->post->create_many(3, array('post_type' => 'post', 'tags_input' => 'zong') );
			$posts = $this->factory->post->create_many(5, array('post_type' => 'portfolio', 'tags_input' => 'zong') );
			$term = new TimberTerm($term_id);
			$posts_gotten = $term->posts('posts_per_page=4');
			$this->assertEquals(4, count($posts_gotten));

			$posts_gotten = $term->posts(array('posts_per_page' => 7));
			$this->assertEquals(7, count($posts_gotten));

		}

		function testGetPostsOld() {
			$term_id = $this->factory->term->create();
			$posts = array();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			$posts[] = $this->factory->post->create();
			foreach($posts as $post_id){
				wp_set_object_terms($post_id, $term_id, 'post_tag', true);
			}
			$term = new TimberTerm($term_id);
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
			$term = new TimberTerm($term_id);
			$gotten_posts = $term->posts(count($posts), 'page');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->posts(count($posts), 'any');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->posts(count($posts), 'post');
			$this->assertEquals(0, count($gotten_posts));
		}

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
			$term = new TimberTerm($term_id);
			$gotten_posts = $term->get_posts('post_type=page');
			$this->assertEquals(count($posts), count($gotten_posts));
			$gotten_posts = $term->get_posts('post_type=page', 'TimberPostSubclass');
			$this->assertEquals(count($posts), count($gotten_posts));
			$this->assertEquals($gotten_posts[0]->foo(), 'bar');
			$gotten_posts = $term->get_posts(array('post_type' => 'page'), 'TimberPostSubclass');
			$this->assertEquals($gotten_posts[0]->foo(), 'bar');
			$this->assertEquals(count($posts), count($gotten_posts));
		}

		function testTermChildren() {
			$parent_id = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
			$local = $this->factory->term->create(array('name' => 'Local', 'parent' => $parent_id, 'taxonomy' => 'category'));
			$int = $this->factory->term->create(array('name' => 'International', 'parent' => $parent_id, 'taxonomy' => 'category'));

			$term = new TimberTerm($parent_id);
			$children = $term->children();
			$this->assertEquals(2, count($children));
			$this->assertEquals('Local', $children[0]->name);
		}

		function testTermEditLink() {
			wp_set_current_user(1);
			$tid = $this->factory->term->create(array('name' => 'News', 'taxonomy' => 'category'));
			$term = new TimberTerm($tid);
			$links = array();

			$links[] = 'http://example.org/wp-admin/term.php?taxonomy=category&tag_ID='.$tid.'&post_type=post';
			$links[] = 'http://example.org/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID='.$tid.'&post_type=post';
			$links[] = 'http://example.org/wp-admin/edit-tags.php?action=edit&taxonomy=category&tag_ID='.$tid;
			$links[] = 'http://example.org/wp-admin/term.php?taxonomy=category&term_id='.$tid.'&post_type=post';
			$this->assertContains($term->edit_link(), $links);
		}

	}
