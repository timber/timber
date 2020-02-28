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

		function testGetSingleTermInTaxonomy() {
			register_taxonomy('cars', 'post');
			$term_id = $this->factory->term->create( array('name' => 'Toyota', 'taxonomy' => 'cars') );
			$term = Timber::get_term($term_id, 'cars');
			$this->assertEquals($term_id, $term->ID);
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
