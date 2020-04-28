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

		// function testGetMultiTerm() {
		// 	register_taxonomy('cars', 'post');
		// 	$term_ids[] = $this->factory->term->create(['name' => 'Toyota', 'taxonomy' => 'cars']);
		// 	$term_ids[] = $this->factory->term->create(['name' => 'Honda', 'taxonomy' => 'cars']);
		// 	$terms = Timber\Term::from($term_ids, 'cars');
		// 	$this->assertEquals($term_ids[0], $terms[0]->ID);
		// }

		// function testGetSingleTermFrom() {
		// 	register_taxonomy('cars', 'post');
		// 	$term_id = $this->factory->term->create(['name' => 'Toyota', 'taxonomy' => 'cars']);
		// 	$term = Timber\Term::from($term_id, 'cars');
		// 	error_log(print_r($term, true));
		// 	$this->assertEquals($term_id, $term->ID);
		// }
	}