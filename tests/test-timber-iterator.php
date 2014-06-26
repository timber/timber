<?php

class TimberIteratorTest extends WP_UnitTestCase {

    function testQueryPosts(){
        $this->factory->post->create();
        $posts = TimberPostGetter::query_posts('post_type=post');
        $this->assertInstanceOf( 'TimberQueryIterator', $posts );
    }

    function testTheLoop(){
        for ( $i = 1; $i < 3; $i++ ) {
            $this->factory->post->create( array(
                'post_title' => 'TestPost' . $i
            ) );
        }
        $results = Timber::compile('assets/iterator-test.twig', array(
            'posts' => TimberPostGetter::query_posts( 'post_type=post' )
        ) );

        $results = trim( $results );
        $this->assertStringStartsWith( 'TestPost2', $results );
        $this->assertStringEndsWith( 'TestPost1', $results );

    }

}