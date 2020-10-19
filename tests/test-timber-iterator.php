<?php

class TestTimberIterator extends Timber_UnitTestCase {

    function testQueryPosts(){
        $this->factory->post->create();
        $posts = TimberPostGetter::query_posts('post_type=post');
        $this->assertInstanceOf( 'TimberQueryIterator', $posts );
    }

    function testTheLoop(){
        for ( $i = 1; $i < 3; $i++ ) {
            $this->factory->post->create( array(
                'post_title' => 'TestPost' . $i,
                'post_date' => ('2018-09-0'.$i.' 01:56:01')
            ) );
        }
        $results = Timber::compile('assets/iterator-test.twig', array(
            'posts' => TimberPostGetter::query_posts( 'post_type=post' )
        ) );

        $results = trim( $results );
        $this->assertStringStartsWith( 'TestPost2', $results );
        $this->assertStringEndsWith( 'TestPost1', $results );

    }

    function testTwigLoopVar() {
	    $posts = $this->factory->post->create_many( 3 );
	    $posts = TimberPostGetter::query_posts($posts);

	    $compiled = Timber::compile('assets/iterator-loop-test.twig', array(
		    'posts' => TimberPostGetter::query_posts( 'post_type=post' )
	    ) );

	    $loop = array_map('json_decode', explode("\n", trim($compiled)));

	    $this->assertSame(1, $loop[0]->index);
	    $this->assertSame(2, $loop[0]->revindex0);
	    $this->assertSame(3, $loop[0]->length);
	    $this->assertTrue($loop[0]->first);
	    $this->assertFalse($loop[0]->last);

	    $this->assertSame(2, $loop[1]->index);
	    $this->assertSame(1, $loop[1]->revindex0);
	    $this->assertSame(3, $loop[1]->length);
	    $this->assertFalse($loop[1]->first);
	    $this->assertFalse($loop[1]->last);

	    $this->assertSame(3, $loop[2]->index);
	    $this->assertSame(0, $loop[2]->revindex0);
	    $this->assertSame(3, $loop[2]->length);
	    $this->assertFalse($loop[2]->first);
	    $this->assertTrue($loop[2]->last);
    }

    function testPostCount() {
    	$posts = $this->factory->post->create_many( 8 );
        $posts = TimberPostGetter::query_posts('post_type=post');
        $this->assertEquals( 8, $posts->post_count() );
        $this->assertEquals( 8, count($posts) );
    }

}
