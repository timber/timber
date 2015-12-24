<?php

	class TestTimberTermGetter extends Timber_UnitTestCase {

		function testGetArrayOfTerms(){
			$term_ids = array();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$terms = Timber::get_terms($term_ids);
			$this->assertEquals(count($term_ids), count($terms));
		}

		function testGetTermsByString() {
			$term_ids = $this->factory->term->create_many(17);
			$terms = Timber::get_terms('tag');
			$this->assertEquals(17, count($terms));
			$terms = Timber::get_terms(array('taxonomies' => 'tag'));
			$this->assertEquals(17, count($terms));
			$terms = Timber::get_terms('taxonomies=tag');
			$this->assertEquals(17, count($terms));
		}

		function testSubclass(){
			$term_ids = array();
			$class_name = 'TimberTermSubclass';
			require_once('php/timber-term-subclass.php');
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$terms = Timber::get_terms($term_ids, $class_name);
			$this->assertEquals($class_name, get_class($terms[0]));
			$terms = false;
			$terms = Timber::get_terms($term_ids, null, $class_name);
			$this->assertEquals($class_name, get_class($terms[0]));
			$terms = false;
			$terms = Timber::get_terms($term_ids, array(), $class_name);
			$this->assertEquals($class_name, get_class($terms[0]));
		}

		function testGetWithQueryString(){
			$other_term = $this->factory->term->create(array('name' => 'Bogus Term'));
			$term_id = $this->factory->term->create(array('name' => 'My Term'));
			$terms = Timber::get_terms('term_id='.$term_id);
			$this->assertEquals($term_id, $terms[0]->ID);
			$terms = Timber::get_terms('post_tag');
			$this->assertEquals(2, count($terms));
			$terms = Timber::get_terms();
			$this->assertEquals(3, count($terms));
			$terms = Timber::get_terms('categories');
			$this->assertEquals(1, count($terms));
			$terms = Timber::get_terms(array('tag'));
			$this->assertEquals(2, count($terms));
			$query = array('taxonomies' => array('category'));
			$terms = Timber::get_terms($query);
			$this->assertEquals('Uncategorized', $terms[0]->name);

			$query = array('tax' => array('category'));
			$terms = Timber::get_terms($query);
			$this->assertEquals('Uncategorized', $terms[0]->name);

			$query = array('taxs' => array('category'));
			$terms = Timber::get_terms($query);
			$this->assertEquals('Uncategorized', $terms[0]->name);

			$query = array('taxonomy' => array('category'));
			$terms = Timber::get_terms($query);
			$this->assertEquals('Uncategorized', $terms[0]->name);

			$next_term = $this->factory->term->create(array('name' => 'Another Term'));
			$terms = Timber::get_terms('post_tag', 'term_id='.$next_term);
			$this->assertEquals('Another Term', $terms[0]->name);

			$terms = Timber::get_terms(array($next_term, $term_id));
			$this->assertEquals(2, count($terms));
			$this->assertEquals('My Term', $terms[1]->name);
		}
	}
