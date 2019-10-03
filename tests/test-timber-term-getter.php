<?php

	class MyTerm extends Timber\Term {}

	class TestTimberTermGetter extends Timber_UnitTestCase {

		function setUp() {
			global $wpdb;
			$query = "truncate $wpdb->term_relationships";
			$wpdb->query($query);
			$query = "truncate $wpdb->term_taxonomy";
			$wpdb->query($query);
			$query = "truncate $wpdb->terms";
			$wpdb->query($query);
			$query = "truncate $wpdb->termmeta";
			$wpdb->query($query);
		}

		function testGetSingleTerm() {
			$term_id = $this->factory->term->create( array('name' => 'Toyota') );
			$term = Timber::get_term($term_id);
			$this->assertEquals($term_id, $term->ID);
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

			$terms = Timber::get_terms('tag');
			$this->assertCount(17, $terms);

			$terms = Timber::get_terms(array('taxonomies' => 'tag'));
			$this->assertCount(17, $terms);
		}

		function testSubclass(){
			$term_ids = $this->factory->term->create_many(4);

			$terms = Timber::get_terms($term_ids, MyTerm::class);
			$this->assertEquals(MyTerm::class, get_class($terms[0]));

			$terms = false;
			$terms = Timber::get_terms($term_ids, null, MyTerm::class);
			$this->assertEquals(MyTerm::class, get_class($terms[0]));

			$terms = false;
			$terms = Timber::get_terms($term_ids, array(), MyTerm::class);
			$this->assertEquals(MyTerm::class, get_class($terms[0]));
		}

		function testGetWithQuery(){
			$category = $this->factory->term->create(array('name' => 'Uncategorized', 'taxonomy' => 'category'));
			$other_term = $this->factory->term->create(array('name' => 'Bogus Term'));
			$term_id = $this->factory->term->create(array('name' => 'My Term'));

			$terms = Timber::get_terms('post_tag');
			$this->assertCount(2, $terms);

			$terms = Timber::get_terms();
			$this->assertCount(3, $terms);

			$terms = Timber::get_terms('categories');
			$this->assertCount(1, $terms);

			$terms = Timber::get_terms(array('tag'));
			$this->assertCount(2, $terms);

			$query = array('taxonomy' => array('category'));
			$terms = Timber::get_terms($query);
			$this->assertEquals('Uncategorized', $terms[0]->name);

			$new_id = $this->factory->term->create(array('name' => 'Another Term'));
			$terms = Timber::get_terms('post_tag', ['term_id' => $new_id]);
			$this->assertEquals('Another Term', $terms[0]->name);

			$terms = Timber::get_terms(array($new_id, $term_id));
			$this->assertCount(2, $terms);
			$this->assertEquals('My Term', $terms[1]->name);
		}
	}
