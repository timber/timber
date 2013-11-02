<?php

	class TimberTermTest extends WP_UnitTestCase {

		function testTerm(){
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertEquals('TimberTerm', get_class($term));
		}

		function testTermInitObject(){
			$term_id = $this->factory->term->create();
			$term = get_term($term_id, 'post_tag');
			$term = new TimberTerm($term);
			$this->assertEquals($term->ID, $term_id);
		}

		function testTermLink(){
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertContains('http://', $term->link());
			$this->assertContains('http://', $term->get_link());
		}

		function testTermPath(){
			$term_id = $this->factory->term->create();
			$term = new TimberTerm($term_id);
			$this->assertFalse(strstr($term->path(), 'http://'));
			$this->assertFalse(strstr($term->get_path(), 'http://'));
		}

	}