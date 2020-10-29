<?php

use Timber\QueryIterator;

/**
 * @group posts-api
 * @group post-collections
 * @todo remove these tests completely?
 */
class TestTimberQueryIterator extends Timber_UnitTestCase {

	function testTheLoop(){
		$this->markTestSkipped('@todo moved to test-timber-post-collection.php');
		foreach (range(1, 3) as $i) {
			$this->factory->post->create( array(
				'post_title' => 'TestPost' . $i,
				'post_date' => ('2018-09-0'.$i.' 01:56:01')
			) );
		}

		$wp_query = new WP_Query('post_type=post');

		$results = Timber::compile_string(
			'{% for p in posts %}{{fn("get_the_title")}}{% endfor %}',
			[
				'posts' => new QueryIterator($wp_query),
			]
		);

		// Assert that our posts show up in reverse-chronological order.
		$this->assertEquals( 'TestPost3TestPost2TestPost1', $results );
	}

	function testTwigLoopVar() {
		$this->markTestSkipped('@todo do we really want to be testing loop internals like this?');
		$this->factory->post->create_many( 3 );

		$wp_query = new WP_Query('post_type=post');

		// Dump the loop object itself each iteration, so we can see its
		// internals over time.
		$compiled = Timber::compile_string(
			"{% for p in posts %}\n{{loop|json_encode}}\n{% endfor %}\n", array(
			'posts' => new QueryIterator($wp_query),
		) );

		// Get each iteration as an object (each should have its own line).
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

}
