<?php

	/**
	* @group terms-api
	*/
	class TestTimberTermFactories extends Timber_UnitTestCase {

		function setUp() {
			$this->truncate('term_relationships');
			$this->truncate('term_taxonomy');
			$this->truncate('terms');
			$this->truncate('termmeta');
		}

		function testGetTerm() {
			$term_id = $this->factory->term->create(['name' => 'Thingo', 'taxonomy' => 'post_tag']);
			$term = Timber::get_term($term_id);
			$this->assertEquals('Thingo', $term->name);
		}

		function testGetMultiTerm() {
			register_taxonomy('cars', 'post');
			$term_ids[] = $this->factory->term->create(['name' => 'Toyota', 'taxonomy' => 'cars']);
			$term_ids[] = $this->factory->term->create(['name' => 'Honda', 'taxonomy' => 'cars']);
			$term_ids[] = $this->factory->term->create(['name' => 'Chevy', 'taxonomy' => 'cars']);
			$post_id = $this->factory->post->create();
			wp_set_object_terms( $post_id, $term_ids, 'cars' );

			$term_get = Timber::get_terms(['taxonomy' => 'cars']);
			$this->assertEquals('Chevy', $term_get[0]->title());

			$terms_from = Timber\Term::from($term_ids, 'cars');
			$this->assertEquals('Chevy', $terms_from[0]->title());
			//$this->assertEquals($term_ids[0], $terms[0]->ID);
		}

		function testGetSingleTermFrom() {
			register_taxonomy('cars', 'post');
			$term_id = $this->factory->term->create(['name' => 'Toyota', 'taxonomy' => 'cars']);
			$post_id = $this->factory->post->create();
			wp_set_object_terms( $post_id, $term_id, 'cars' );

			$term_get = Timber::get_term(['taxonomy' => 'cars']);
			$this->assertEquals($term_id, $term_get[0]->ID);

			$term_from = Timber\Term::from($term_id, 'cars');
			$this->assertEquals($term_id, $term_from[0]->ID);
		}
	}