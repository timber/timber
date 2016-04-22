<?php

	class TestTimberPostTerms extends Timber_UnitTestCase {

		function testPostTerms() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);

			// create a new tag and associate it with the post
			$dummy_tag = wp_insert_term('whatever', 'post_tag');
			wp_set_object_terms($pid, $dummy_tag['term_id'], 'post_tag', true);

			$terms = $post->get_terms('post_tag', 'MyTimberTerm');
			$this->assertEquals( 'MyTimberTerm', get_class($terms[0]) );

			$post = new TimberPost($pid);
			$terms = $post->terms('post_tag', true, 'MyTimberTerm');
			$this->assertEquals( 'MyTimberTerm', get_class($terms[0]) );

		}

		function testTermExceptions() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$terms = $post->get_terms('foobar');
			$this->assertEquals(array(), $terms);
		}

		function testTermNotMerged() {
			$pid = $this->factory->post->create();
			// create a new tag and associate it with the post
			$dummy_tag = wp_insert_term('whatever', 'post_tag');
			wp_set_object_terms($pid, $dummy_tag['term_id'], 'post_tag', true);

			$dummy_cat = wp_insert_term('thingy', 'category');
			wp_set_object_terms($pid, $dummy_cat['term_id'], 'category', true);

			$post = new TimberPost($pid);
			$terms = $post->terms('all', false);
			$this->assertEquals($terms['post_tag'][0]->name, 'whatever');

		}

	}

	class MyTimberTerm extends TimberTerm {

	}
