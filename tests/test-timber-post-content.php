<?php

	class TestTimberPostContent extends Timber_UnitTestCase {


	function testContent(){
		$quote = 'The way to do well is to do well.';
		$post_id = $this->factory->post->create();
		$post = new TimberPost($post_id);
		$post->post_content = $quote;
		wp_update_post($post);
		$this->assertEquals($quote, trim(strip_tags($post->content())));
		$this->assertEquals($quote, trim(strip_tags($post->get_content())));
	}

	function testContentPaged(){
		$quote = $page1 = 'The way to do well is to do well.';
		$quote .= '<!--nextpage-->';
		$quote .= $page2 = "And do not let your tongue get ahead of your mind.";

		$post_id = $this->factory->post->create();
		$post = new TimberPost($post_id);
		$post->post_content = $quote;
		wp_update_post($post);

		$this->assertEquals($page1, trim(strip_tags($post->content(1))));
		$this->assertEquals($page2, trim(strip_tags($post->content(2))));
		$this->assertEquals($page1, trim(strip_tags($post->get_content(0,1))));
		$this->assertEquals($page2, trim(strip_tags($post->get_content(0,2))));
	}

	function testPagedContent(){
		$quote = $page1 = 'Named must your fear be before banish it you can.';
		$quote .= '<!--nextpage-->';
		$quote .= $page2 = "No, try not. Do or do not. There is no try.";

		$post_id = $this->factory->post->create(array('post_content' => $quote));

		$this->go_to( get_permalink( $post_id ) );

		// @todo The below should work magically when the iterators are merged
		setup_postdata( get_post( $post_id ) );

		$post = Timber::get_post();
			$this->assertEquals($page1, trim(strip_tags( $post->paged_content() )));

		$pagination = $post->pagination();
		$this->go_to( $pagination['pages'][1]['link'] );

		setup_postdata( get_post( $post_id ) );
		$post = Timber::get_post();

		$this->assertEquals($page2, trim(strip_tags( $post->get_paged_content() )));
	}

	/**
	 * @ticket 2218
	 */
	function testGutenbergExcerptOption() {
		global $wp_version;
		if ( $wp_version < 5.0 ) {
			$this->markTestSkipped('Only applies to Block editor which is avaialble in WP 5.x');
		}
		$content_1 = '<!-- wp:paragraph --><p>Here is the start to my post! This should not show when noTeaser:true</p><!-- /wp:paragraph -->
<!-- wp:more {"noTeaser":true} --><!--more--><!--noteaser-->';
		$content_2 = '<!-- /wp:more --><!-- wp:paragraph --><p>WHEN noTeaser:true, ONLY this shows on the single page</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>And this too!</p><!-- /wp:paragraph -->';
		$post_id = $this->factory->post->create(['post_content' => $content_1.$content_2 ]);
		$post = new \Timber\Post($post_id);
		
		$this->assertEquals($content_2, $post->content());
	}

}