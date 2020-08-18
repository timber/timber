<?php

use Timber\PostArrayObject;
use Timber\PostQuery;

require_once 'php/SerializablePost.php';

/**
 * @group posts-api
 * @group post-collections
 */
class TestTimberPostArrayObject extends Timber_UnitTestCase {

	function setUp() {
		global $wpdb;
		$wpdb->query("TRUNCATE TABLE $wpdb->posts");
		$wpdb->query("ALTER TABLE $wpdb->posts AUTO_INCREMENT = 1");
		parent::setUp();
  }

  function testEmpty() {
    $coll = new PostArrayObject([]);

    $this->assertCount(0, $coll);
  }

  function testCount() {
    $this->factory->post->create_many( 20 );
    $query = new Timber\PostQuery([
      'query' => [
        'post_type'      => 'post',
        'posts_per_page' => -1,
      ],
    ]);

    $coll = new PostArrayObject($query->to_array());

    $this->assertCount(20, $coll);
  }

  function testPagination() {
    $this->factory->post->create_many( 20 );
    $query = new Timber\PostQuery([
      'query' => [
        'post_type'      => 'post',
      ],
    ]);

    $coll = new PostArrayObject($query->to_array());

    $this->assertNull($coll->pagination());
  }

  function testJsonSerialize() {
		$this->factory->post->create([
			'post_title' => 'Tobias',
			'post_type'  => 'funke',
			'meta_input' => [
				'how_many_of_us' => 'DOZENS',
			],
		]);

		$this->add_filter_temporarily('timber/post/classmap', function() {
			return [
				'funke' => SerializablePost::class,
			];
		});

		$query = new PostQuery([
			'query' => new WP_Query('post_type=>funke'),
    ]);

    $coll = new PostArrayObject($query->to_array());

		$this->assertEquals([
			[
				'post_title'     => 'Tobias',
				'post_type'      => 'funke',
				'how_many_of_us' => 'DOZENS',
			],
		], json_decode(json_encode($coll), true));
  }

}