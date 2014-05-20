<?php

	class TestTimberSidebar extends WP_UnitTestCase {

		function testTwigSidebar(){
			$context = Timber::get_context();
			$sidebar_post = $this->factory->post->create(array('post_title' => 'Sidebar post content'));
			$sidebar_context = array();
			$sidebar_context['post'] = new TimberPost($sidebar_post);
			$context['sidebar'] = Timber::get_sidebar('assets/sidebar.twig', $sidebar_context);
			$result = Timber::compile('assets/main-w-sidebar.twig', $context);
			$this->assertEquals('I am the main stuff <h4>Sidebar post content</h4>', trim($result));
		}

	}
