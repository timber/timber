<?php
	
	class TestTimberPostGetter extends WP_UnitTestCase {

		function testGetPostsInLoop(){
			$posts = $this->factory->post->create_many(55);
			$this->go_to('/');
			$start = microtime(true);
			if ( have_posts() ) {
				while(have_posts()){
					the_post();
					$posts = Timber::get_posts();
				}
			}
			$end = microtime(true);
			$diff = $end - $start;
			//if this takes more than 3 seconds, we're in trouble
			$this->assertLessThan(3, $diff);
		}

	}
