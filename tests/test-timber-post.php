<?php

	class TimberPostTest extends WP_UnitTestCase {

		function testPost(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$this->assertEquals('TimberPost', get_class($post));
			$this->assertEquals($post_id, $post->ID);
		}

		function testComments() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$comment_id_array = $this->factory->comment->create_many( 5, array('comment_post_ID' => $post_id) );
			$post = new TimberPost($post_id);
			$this->assertEquals( 5, count($post->get_comments()) );
		}

		function testNameMethod() {
			$post_id = $this->factory->post->create(array('post_title' => 'Battlestar Galactica'));
			$post = new TimberPost($post_id);
			$this->assertEquals('Battlestar Galactica', $post->name());
		}

		function testGetImage() {
			$post_id = $this->factory->post->create(array('post_title' => 'St. Louis History'));
			$filename = TimberImageTest::copyTestImage( 'arch.jpg' );
			$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
			$iid = wp_insert_attachment( $attachment, $filename, $post_id );
			update_post_meta($post_id, 'landmark', $iid);
			$post = new TimberPost($post_id);
			$image = $post->get_image('landmark');
			$this->assertEquals('The Arch', $image->title());
		}

		function testPostString() {
			$post_id = $this->factory->post->create(array('post_title' => 'Gobbles'));
			$post = new TimberPost($post_id);
			$str = Timber::compile_string('<h1>{{post}}</h1>', array('post' => $post));
			$this->assertEquals('<h1>Gobbles</h1>', $str);
		}

		function testFalseParent() {
			$pid = $this->factory->post->create();
			$filename = TimberImageTest::copyTestImage( 'arch.jpg' );
			$attachment = array( 'post_title' => 'The Arch', 'post_content' => '' );
			$iid = wp_insert_attachment( $attachment, $filename, $pid );
			update_post_meta( $iid, 'architect', 'Eero Saarinen' );
			$image = new TimberImage( $iid );
			$parent = $image->parent();
			$this->assertEquals($pid, $parent->ID);
			$this->assertFalse($parent->parent());
		}

		function testPostOnSingle(){
			$post_id = $this->factory->post->create();
			$this->go_to(home_url('/?p='.$post_id));
			$post = new TimberPost();
			$this->assertEquals($post_id, $post->ID);
		}

		function testPostOnSingleQuery(){
			$post_id = $this->factory->post->create();
			$this->go_to(home_url('/?p='.$post_id));
			$post_id = $this->factory->post->create();
			$post = Timber::query_post($post_id);
			$this->assertEquals($post_id, $post->ID);
			$this->assertEquals($post_id, get_the_ID());
		}

		function testPostOnSingleQueryNoParams(){
			$post_id = $this->factory->post->create();
			$this->go_to(home_url('/?p='.$post_id));
			$post = Timber::query_post();
			$this->assertEquals($post_id, $post->ID);
			$this->assertEquals($post_id, get_the_ID());
		}

		// function testPostOnBuddyPressPage(){
		// 	$post_id = $this->factory->post->create();
		// 	global $post;
		// 	$this->go_to(home_url('/?p='.$post_id));
		// 	$_post = $post;
		// 	$post = false;
		// 	$my_post = new TimberPost();
		// 	$this->assertEquals($post_id, $my_post->ID);
		// }

		function testNonexistentProperty(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost( $post_id );
			$this->assertFalse( $post->zebra );
		}

		function testNonexistentMethod(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost( $post_id );
			$this->assertFalse( $post->donkey() );
		}

		function testNext(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$this->assertEquals($firstPost->next()->ID, $nextPost->ID);
		}

		function testNextCategory(){
			$posts = array();
			for($i = 0; $i<4; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			wp_set_object_terms($posts[0], 'TestMe', 'category', false);
			wp_set_object_terms($posts[2], 'TestMe', 'category', false);
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[2]);
			$this->assertEquals($firstPost->next('category')->ID, $nextPost->ID);
		}

		function testNextCustomTax(){
			$v = get_bloginfo('version');
			if (version_compare($v, '3.8', '<')) {
           		$this->markTestSkipped('Custom taxonomy prev/next not supported until 3.8');
        	} else {
				register_taxonomy('pizza', 'post');
				$posts = array();
				for($i = 0; $i<4; $i++){
					$j = $i + 1;
					$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
				}
				wp_set_object_terms($posts[0], 'Cheese', 'pizza', false);
				wp_set_object_terms($posts[2], 'Cheese', 'pizza', false);
				wp_set_object_terms($posts[3], 'Mushroom', 'pizza', false);
				$firstPost = new TimberPost($posts[0]);
				$nextPost = new TimberPost($posts[2]);
				$this->assertEquals($firstPost->next('pizza')->ID, $nextPost->ID);
			}
		}

		function testPrev(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$lastPost = new TimberPost($posts[1]);
			$prevPost = new TimberPost($posts[0]);
			$this->assertEquals($lastPost->prev()->ID, $prevPost->ID);
		}

		function testPrevCustomTax(){
			$v = get_bloginfo('version');
			if (version_compare($v, '3.8', '<')) {
           		$this->markTestSkipped('Custom taxonomy prev/next not supported until 3.8');
        	} else {
				register_taxonomy('pizza', 'post');
				$posts = array();
				for($i = 0; $i<3; $i++){
					$j = $i + 1;
					$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
				}
				wp_set_object_terms($posts[0], 'Cheese', 'pizza', false);
				wp_set_object_terms($posts[2], 'Cheese', 'pizza', false);
				$lastPost = new TimberPost($posts[2]);
				$prevPost = new TimberPost($posts[0]);
				$this->assertEquals($lastPost->prev('pizza')->ID, $prevPost->ID);
			}
		}

		function testPrevCategory(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			wp_set_object_terms($posts[0], 'TestMe', 'category', false);
			wp_set_object_terms($posts[2], 'TestMe', 'category', false);
			$lastPost = new TimberPost($posts[2]);
			$prevPost = new TimberPost($posts[0]);
			$this->assertEquals($lastPost->prev('category')->ID, $prevPost->ID);
		}

		function testNextWithDraftAndFallover(){
			$posts = array();
			for($i = 0; $i<3; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$nextPostAfter = new TimberPost($posts[2]);
			$nextPost->post_status = 'draft';
			wp_update_post($nextPost);
			$this->assertEquals($firstPost->next()->ID, $nextPostAfter->ID);
		}

		function testNextWithDraft(){
			$posts = array();
			for($i = 0; $i<2; $i++){
				$j = $i + 1;
				$posts[] = $this->factory->post->create(array('post_date' => '2014-02-0'.$j.' 12:00:00'));
			}
			$firstPost = new TimberPost($posts[0]);
			$nextPost = new TimberPost($posts[1]);
			$nextPost->post_status = 'draft';
			wp_update_post($nextPost);
			$nextPostTest = $firstPost->next();
		}

		function testPostInitObject(){
			$post_id = $this->factory->post->create();
			$post = get_post($post_id);
			$post = new TimberPost($post);
			$this->assertEquals($post->ID, $post_id);
		}

		function testPostByName(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$pid_from_name = TimberPost::get_post_id_by_name($post->post_name);
			$this->assertEquals($pid_from_name, $post_id);
		}

		function testUpdate(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$rand = rand_str();
			$post->update('test_meta', $rand);
			$post = new TimberPost($post_id);
			$this->assertEquals($rand, $post->test_meta);
		}

		function testDoubleEllipsis(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_excerpt = 'this is super dooper trooper long words';
			$prev = $post->get_preview(3, true);
			$this->assertEquals(1, substr_count($prev, '&hellip;'));
		}

		function testCanEdit(){
			wp_set_current_user(1);
			$post_id = $this->factory->post->create(array('post_author' => 1));
			$post = new TimberPost($post_id);
			$this->assertTrue($post->can_edit());
			wp_set_current_user(0);
		}

		function testGetPreview() {
			global $wp_rewrite;
			$struc = false;
			$wp_rewrite->permalink_structure = $struc;
			update_option('permalink_structure', $struc);
			$post_id = $this->factory->post->create(array('post_content' => 'this is super dooper trooper long words'));
			$post = new TimberPost($post_id);

			// no excerpt
			$post->post_excerpt = '';
			$preview = $post->get_preview(3);
			$this->assertRegExp('/this is super &hellip;  <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Read More<\/a>/', $preview);

			// excerpt set, force is false, no read more
			$post->post_excerpt = 'this is excerpt longer than three words';
			$preview = $post->get_preview(3, false, '');
			$this->assertEquals($preview, $post->post_excerpt);

			// custom read more set
			$post->post_excerpt = '';
			$preview = $post->get_preview(3, false, 'Custom more');
			$this->assertRegExp('/this is super &hellip;  <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Custom more<\/a>/', $preview);

			// content with <!--more--> tag, force false
			$post->post_content = 'this is super dooper<!--more--> trooper long words';
			$preview = $post->get_preview(2, false, '');
			$this->assertEquals('this is super dooper', $preview);
		}

		function testTitle(){
			$title = 'Fifteen Million Merits';
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$post->post_title = $title;
			wp_update_post($post);
			$this->assertEquals($title, trim(strip_tags($post->title())));
			$this->assertEquals($title, trim(strip_tags($post->get_title())));
		}

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
			$this->assertEquals($page1, trim(strip_tags($post->get_paged_content())));

            $pagination = $post->pagination();
            $this->go_to( $pagination['pages'][1]['link'] );

            setup_postdata( get_post( $post_id ) );
            $post = Timber::get_post();

			$this->assertEquals($page2, trim(strip_tags($post->get_paged_content())));
		}

		function testMetaCustomArrayFilter(){
			add_filter('timber_post_get_meta', function($customs){
				foreach($customs as $key=>$value){
					$flat_key = str_replace('-', '_', $key);
					$flat_key .= '_flat';
					$customs[$flat_key] = $value;
				}
				// print_r($customs);
				return $customs;
			});
			$post_id = $this->factory->post->create();
			update_post_meta($post_id, 'the-field-name', 'the-value');
			update_post_meta($post_id, 'with_underscores', 'the_value');
			$post = new TimberPost($post_id);
			$this->assertEquals($post->with_underscores_flat, 'the_value');
			$this->assertEquals($post->the_field_name_flat, 'the-value');
		}

		function testPostMetaMetaException(){
			$post_id = $this->factory->post->create();
			$post = new TimberPost($post_id);
			$string = Timber::compile_string('My {{post.meta}}', array('post' => $post));
			$this->assertEquals('My', trim($string));
			update_post_meta($post_id, 'meta', 'steak');
			$post = new TimberPost($post_id);
			$string = Timber::compile_string('My {{post.custom.meta}}', array('post' => $post));
			//sorry you can't over-write methods now
			$this->assertEquals('My steak', trim($string));
		}

		function testPostParent(){
			$parent_id = $this->factory->post->create();
			$child_id = $this->factory->post->create(array('post_parent' => $parent_id));
			$child_post = new TimberPost($child_id);
			$this->assertEquals($parent_id, $child_post->parent()->ID);
		}

		function testPostSlug(){
			$pid = $this->factory->post->create(array('post_name' => 'the-adventures-of-tom-sawyer'));
			$post = new TimberPost($pid);
			$this->assertEquals('the-adventures-of-tom-sawyer', $post->slug);
		}

		function testPostAuthor(){
			$author_id = $this->factory->user->create(array('display_name' => 'Jared Novack'));
			$pid = $this->factory->post->create(array('post_author' => $author_id));
			$post = new TimberPost($pid);
			$this->assertEquals('user-1', $post->author()->slug());
			$this->assertEquals('Jared Novack', $post->author()->name());
			$template = 'By {{post.author}}';
			$authorCompile = Timber::compile_string($template, array('post' => $post));
			$template = 'By {{post.author.name}}';
			$authorNameCompile = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals($authorCompile, $authorNameCompile);
			$this->assertEquals('By Jared Novack', $authorCompile);
		}

		function testPostAuthorInTwig(){
			$author_id = $this->factory->user->create(array('display_name' => 'User 1'));
			$pid = $this->factory->post->create(array('post_author' => $author_id));
			$post = new TimberPost($pid);
			$this->assertEquals('user-1', $post->author()->slug());
			$this->assertEquals('User 1', $post->author()->name());
			$template = 'By {{post.author}}';
			$authorCompile = Timber::compile_string($template, array('post' => $post));
			$template = 'By {{post.author.name}}';
			$authorNameCompile = Timber::compile_string($template, array('post' => $post));
			$this->assertEquals($authorCompile, $authorNameCompile);
			$this->assertEquals('By User 1', $authorCompile);
		}

		function testPostModifiedAuthor() {
			$author_id = $this->factory->user->create(array('display_name' => 'Woodward'));
			$mod_author_id = $this->factory->user->create(array('display_name' => 'Bernstein'));
			$pid = $this->factory->post->create(array('post_author' => $author_id));
			$post = new TimberPost($pid);
			$this->assertEquals('user-1', $post->author()->slug());
			$this->assertEquals('user-1', $post->modified_author()->slug());
			$this->assertEquals('Woodward', $post->author()->name());
			$this->assertEquals('Woodward', $post->modified_author()->name());
			update_post_meta($pid, '_edit_last', $mod_author_id);
			$this->assertEquals('user-1', $post->author()->slug());
			$this->assertEquals('user-2', $post->modified_author()->slug());
			$this->assertEquals('Woodward', $post->author()->name());
			$this->assertEquals('Bernstein', $post->modified_author()->name());
		}

		function testPostClass(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$this->assertEquals('post-'.$pid.' post type-post status-publish format-standard hentry category-uncategorized', $post->class);
		}

		function testPostChildren(){
			$parent_id = $this->factory->post->create();
			$children = $this->factory->post->create_many(8, array('post_parent' => $parent_id));
			$parent = new TimberPost($parent_id);
			$this->assertEquals(8, count($parent->children()));
		}

		function testPostNoConstructorArgument(){
			$pid = $this->factory->post->create();
			$this->go_to('?p='.$pid);
			$post = new TimberPost();
			$this->assertEquals($pid, $post->ID);
		}

		function testPostPathUglyPermalinks(){
			update_option('permalink_structure', '');
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$this->assertEquals('http://example.org/?p='.$pid, $post->link());
			$this->assertEquals('/?p='.$pid, $post->path());
		}

		function testPostPathPrettyPermalinks(){
			$struc = '/blog/%year%/%monthnum%/%postname%/';
			update_option('permalink_structure', $struc);
			$pid = $this->factory->post->create(array('post_date' => '2014-05-28'));
			$post = new TimberPost($pid);
			$this->assertStringStartsWith('http://example.org/blog/2014/05/post-title', $post->permalink());
			$this->assertStringStartsWith('/blog/2014/05/post-title', $post->path());
		}

		function testPostCategory(){
			$cat = wp_insert_term('News', 'category');
			$pid = $this->factory->post->create();
			wp_set_object_terms($pid, $cat['term_id'], 'category');
			$post = new TimberPost($pid);
			$this->assertEquals('News', $post->category()->name);
		}

		function testPostCategories(){
			$cats = array('News', 'Sports', 'Obits');
			foreach($cats as &$cat){
				$cat = wp_insert_term($cat, 'category');
			}
			$pid = $this->factory->post->create();
			foreach($cats as $cat){
				wp_set_object_terms($pid, $cat['term_id'], 'category', true);
			}
			$post = new TimberPost($pid);
			$this->assertEquals(3, count($post->categories()));
		}

		function testPostTerms(){
			register_taxonomy('team', 'post');
			$teams = array('Patriots', 'Bills', 'Dolphins', 'Jets');
			foreach($teams as &$team){
				$team_terms[] = wp_insert_term($team, 'team');
			}
			$pid = $this->factory->post->create();
			foreach($team_terms as $team){
				wp_set_object_terms($pid, $team['term_id'], 'team', true);
			}
			$post = new TimberPost($pid);
			$teams = $post->terms('team');
			$this->assertEquals(4, count($teams));
			$tag = wp_insert_term('whatever', 'post_tag');
			wp_set_object_terms($pid, $tag['term_id'], 'post_tag', true);
			$post = new TimberPost($pid);
			$this->assertEquals(6, count($post->terms()));
			$tags = $post->tags();
			$this->assertEquals('whatever', $tags[0]->slug);
			$tags = $post->terms('tag');
			$this->assertEquals('whatever', $tags[0]->slug);
			$this->assertTrue($post->has_term('Dolphins'));
			$this->assertTrue($post->has_term('Patriots', 'team'));
		}

	}
