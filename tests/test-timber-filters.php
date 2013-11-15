<?php
	
	class TestTimberFilters extends WP_UnitTestCase {

		function testPostMetaFieldFilter(){
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'Frank', 'Drebin');
			$tp = new TimberPost($post_id);
			add_filter('timber_post_get_meta_field', function( $value, $pid, $field_name, $timber_post) use ($post_id){
				$this->assertEquals($field_name, 'Frank');
				$this->assertEquals($pid, $post_id);
				$this->assertEquals($timber_post->ID, $post_id);
			});
			$this->assertEquals('Drebin', $tp->meta('Frank'));
		}

	}