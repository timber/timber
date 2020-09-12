<?php

	require_once 'php/MetaTerm.php';

	/**
	 * @group terms-api
	 */
	class TestTimberTermGetter extends Timber_UnitTestCase {

		function setUp() {
			$this->truncate('term_relationships');
			$this->truncate('term_taxonomy');
			$this->truncate('terms');
			$this->truncate('termmeta');
		}

		function testGetSingleTerm() {
			$term_id = $this->factory->term->create( array('name' => 'Toyota') );
			$term = Timber::get_term($term_id);
			$this->assertEquals($term_id, $term->ID);
		}

		function testIDDataType() {
			$term_id = $this->factory->term->create( array('name' => 'Honda') );
			$term = Timber::get_term($term_id);
			$this->assertEquals('integer', gettype($term->id));
			$this->assertEquals('integer', gettype($term->ID));
		}

		/*
		 * Tests taxonomy size: 1, arguments: 1.x style 
		 */
		function testGetSingleTermInTaxonomy() {
			register_taxonomy('cars', 'post');
			$tags_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'post_tag') );
			$cars_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'cars') );
			$cats_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'category') );
			$term = Timber::get_term($cars_id, 'cars');
			$this->assertEquals($cars_id, $term->ID);
			$this->assertEquals('cars', $term->taxonomy);
			$this->assertEquals('Toyota', $term->name);
		}

		/*
		 * Tests taxonomy size: 1, arguments: array
		 */
		function testGetSingleTermInTaxonomyViaArray() {
			register_taxonomy('cars', 'post');
			$tags_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'post_tag') );
			$cars_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'cars') );
			$cats_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'category') );
			$term = Timber::get_terms(['taxonomy' => 'cars']);
			$this->assertEquals($cars_id, $term->ID);
			$this->assertEquals('cars', $term->taxonomy);
			$this->assertEquals('Toyota', $term->name);
		}

		/*
		 * Tests taxonomy size: many, arguments: 1.x style
		 */
		function testGetTermsInTaxonomy() {
			register_taxonomy('cars', 'post');
			$tags_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'post_tag') );
			$cars[] = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Honda', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Tesla', 'taxonomy' => 'cars') );
			$cats_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'category') );

			wp_set_object_terms(
				$this->factory->post->create(),
				$cars,
				'cars'
			);
			$terms = Timber::get_terms('cars');
			$this->assertEquals(3, count($terms));
			$this->assertEquals('cars', $terms[0]->taxonomy);
		}

		/*
		 * Tests taxonomy size: many, arguments: array
		 */
		function testGetTermInsTaxonomyViaArray() {
			register_taxonomy('cars', 'post');
			$tags_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'post_tag') );
			$cars[] = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Honda', 'taxonomy' => 'cars') );
			$cars[] = $this->factory->term->create( array('name' => 'Tesla', 'taxonomy' => 'cars') );
			$cats_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'category') );
			$terms = Timber::get_terms(['taxonomy' => 'cars', 'hide_empty' => false]);
			$this->assertEquals(3, count($terms));
			$this->assertEquals('cars', $terms[0]->taxonomy);
		}


		function testGetArrayOfTerms(){
			$term_ids = $this->factory->term->create_many(5);
			$terms = Timber::get_terms($term_ids);
			$this->assertCount(5, $terms);
		}

		function testGetTermsByString() {
			$term_ids = $this->factory->term->create_many(17);

			// by default hide_empty is true, so assign each term to a post
			wp_set_object_terms(
				$this->factory->post->create(),
				$term_ids,
				'post_tag'
			);

			$terms = Timber::get_terms('tag');
			$this->assertCount(17, $terms);
		}

		function testSubclass(){
			$term_ids = $this->factory->term->create_many(4);

			$this->add_filter_temporarily('timber/term/classmap', function() {
				return [
					'post_tag' => MetaTerm::class,
				];
			});

			$terms = Timber::get_terms($term_ids);
			$this->assertInstanceOf(MetaTerm::class, $terms[0]);
		}

		function testGetWithQuery(){
			$term_ids = [
				$this->factory->term->create(array('name' => 'Uncategorized', 'taxonomy' => 'category')),
				$this->factory->term->create(array('name' => 'Bogus Term')),
				$this->factory->term->create(array('name' => 'My Term')),
			];

			// by default hide_empty is true, so assign each term to a post
			wp_set_object_terms(
				$this->factory->post->create(),
				$term_ids,
				'post_tag'
			);

			$terms = Timber::get_terms('post_tag');
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms();
			$this->assertCount(3, $terms);

			$terms = Timber::get_terms([
				'taxonomy'   => 'category',
			]);
			$this->assertEquals('Uncategorized', $terms[0]->name);
			$this->assertEquals(1, count($terms));
		}

		function testGetTermsWithCorrections() {
			$term_ids = [
				$this->factory->term->create(array('name' => 'Uncategorized', 'taxonomy' => 'category')),
				$this->factory->term->create(array('name' => 'Bogus Term',    'taxonomy' => 'post_tag')),
				$this->factory->term->create(array('name' => 'My Term',       'taxonomy' => 'post_tag')),
			];

			// by default hide_empty is true, so assign each term to a post
			wp_set_object_terms(
				$this->factory->post->create(),
				$term_ids,
				'post_tag'
			);

			$terms = Timber::get_terms('categories');
			$this->assertCount(1, $terms);

			$terms = Timber::get_terms(['tags']);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms(['tag']);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms([
				'taxonomies' => 'post_tag',
			]);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms([
				'tax'        => 'post_tag',
			]);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms([
				'taxs'       => 'post_tag',
			]);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms([
				'taxonomies' => 'tag',
			]);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms([
				'taxonomies' => ['tag'],
			]);
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms([
				// this should get corrected to "include"
				'term_id'    => $term_ids[1],
				'hide_empty' => false,
			]);
			$this->assertEquals('Bogus Term', $terms[0]->name);
		}
	}
