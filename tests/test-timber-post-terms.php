<?php

	class TestTimberPostTerms extends Timber_UnitTestCase {

		function testPostTerms() {
			$pid = $this->factory->post->create();
			$post = new Timber\Post($pid);

			// create a new tag and associate it with the post
			$dummy_tag = wp_insert_term('whatever', 'post_tag');
			wp_set_object_terms($pid, $dummy_tag['term_id'], 'post_tag', true);

			$terms = $post->terms( array(
				'query' => array(
					'taxonomy' => 'post_tag'
				),
				'term_class' => 'MyTimberTerm',
			) );
			$this->assertEquals( 'MyTimberTerm', get_class($terms[0]) );

			$post = new Timber\Post($pid);
			$terms = $post->terms( array(
				'query' => array(
					'taxonomy' => 'post_tag',
				),
				'merge' => true,
				'term_class' => 'MyTimberTerm',
			) );
			$this->assertEquals( 'MyTimberTerm', get_class($terms[0]) );
		}

		/**
		 * @ticket #2163
		 * This test confirms that term ordering works when sent through the query parameter of 
		 * arguments.
		 */
		function testPostTermOrder() {
			$pid = $this->factory->post->create();
			register_taxonomy('cars', 'post');
			$cars[] = $this->factory->term->create( array('name' => 'Honda Civic', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Toyota Corolla', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Toyota Camry', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Dodge Intrepid', 'taxonomy' => 'cars') );
			foreach($cars as $tid) {
				$car = new Timber\Term($tid);
				error_log($tid . ' = ' .$car->name);
			}
			wp_set_object_terms($pid, $cars, 'cars', false);
			$post = new Timber\Post($pid);
			$template = "{% for term_item in post.terms({query : {taxonomy: 'cars', orderby: 'term_id', order: 'ASC'}}) %}{{ term_item.name }} {% endfor %}";
			$str = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals('Honda Civic Toyota Corolla Toyota Camry Dodge Intrepid ', $str);
		}

		/**
		 * This should return an error because the "dfasdf" taxonomy doesn't exist
		 */
		function testTermExceptions() {
			self::enable_error_log(false);
			$pid = $this->factory->post->create();
			$post = new Timber\Post($pid);
			$terms = $post->terms('dfasdf');
			$this->assertInstanceOf('WP_Error', $terms);
			self::enable_error_log(true);
		}

		/**
		 * This shouldn't return an error because the "foobar" taxonomy DOES exist
		 */
		function testTermFromNonExistentTaxonomy() {
			self::enable_error_log(false);
			register_taxonomy('foobar', 'post');
			$pid = $this->factory->post->create();
			$post = new Timber\Post($pid);
			$terms = $post->terms('foobar');
			$this->assertEquals(array(), $terms);
			self::enable_error_log(true);
		}

		function testTermNotMerged() {
			$pid = $this->factory->post->create();
			// create a new tag and associate it with the post
			$dummy_tag = wp_insert_term('whatever', 'post_tag');
			wp_set_object_terms($pid, $dummy_tag['term_id'], 'post_tag', true);

			$dummy_cat = wp_insert_term('thingy', 'category');
			wp_set_object_terms($pid, $dummy_cat['term_id'], 'category', true);

			$post = new Timber\Post($pid);
			$terms = $post->terms( array(
				'query' => array(
					'taxonomy' => 'all'
				),
				'merge' => false,
			) );
			$this->assertEquals($terms['post_tag'][0]->name, 'whatever');
		}

	}

	class MyTimberTerm extends Timber\Term {

	}
