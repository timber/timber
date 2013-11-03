<?php

	class TimberTermGetterTest extends WP_UnitTestCase {

		function testGetArrayOfTerms(){
			$term_ids = array();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$term_ids[] = $this->factory->term->create();
			$terms = Timber::get_terms($term_ids);
			$this->assertEquals(count($term_ids), count($terms));
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
	}