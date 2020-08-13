<?php

use Timber\PostArrayObject;

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

    $coll = new PostArrayObject($query->get_posts());

    $this->assertCount(20, $coll);
  }

  function testPagination() {
    $this->factory->post->create_many( 20 );
    $query = new Timber\PostQuery([
      'query' => [
        'post_type'      => 'post',
      ],
    ]);

    $coll = new PostArrayObject($query->get_posts());

    $this->assertNull($coll->pagination());
  }

}