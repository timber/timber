<?php

use Timber\PostArrayObject;
use Timber\PostQuery;

require_once 'php/CollectionTestPage.php';
require_once 'php/CollectionTestPost.php';
require_once 'php/CollectionTestCustom.php';
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

	function testArrayAccess() {
		// Posts are titled in reverse-chronological order.
		$this->factory->post->create([
			'post_title' => 'Post 2',
			'post_date'  => '2020-01-01',
		]);
		$this->factory->post->create([
			'post_title' => 'Post 1',
			'post_date'  => '2020-01-02',
		]);
		$this->factory->post->create([
			'post_title' => 'Post 0',
			'post_date'  => '2020-01-03',
		]);

		// @todo once the Posts API uses Factories, simplify this to Timber::get_posts([...])
		$wp_query = new WP_Query('post_type=post');
    
    $collection = new PostArrayObject($wp_query->posts);

		$this->assertEquals('Post 0', $collection[0]->title());
		$this->assertEquals('Post 1', $collection[1]->title());
		$this->assertEquals('Post 2', $collection[2]->title());
	}

	function testIterationWithClassMaps() {
		// Posts are titled in reverse-chronological order.
		$this->factory->post->create([
			'post_date'  => '2020-01-03',
			'post_type'  => 'custom',
		]);
		$this->factory->post->create([
			'post_date'  => '2020-01-02',
			'post_type'  => 'page',
		]);
		$this->factory->post->create([
			'post_date'  => '2020-01-01',
			'post_type'  => 'post',
		]);

		$this->add_filter_temporarily('timber/post/classmap', function() {
			return [
				'post'   => CollectionTestPost::class,
				'page'   => CollectionTestPage::class,
				'custom' => CollectionTestCustom::class,
			];
		});

    $wp_query = new WP_Query([
      'post_type' => ['post', 'page', 'custom']
    ]);
    
		$collection = new PostArrayObject($wp_query->posts);
		
		// Test that iteration realizes the correct class.
		$expected = [
			CollectionTestCustom::class,
			CollectionTestPage::class,
			CollectionTestPost::class,
		];
		foreach ($collection as $idx => $post) {
			$this->assertInstanceOf($expected[$idx], $post);
		}
	}

  function testJsonSerialize() {
    $this->markTestSkipped();
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

		$wp_query = new WP_Query('post_type=>funke');

    $coll = new PostArrayObject($wp_query->posts);

		$this->assertEquals([
			[
				'post_title'     => 'Tobias',
				'post_type'      => 'funke',
				'how_many_of_us' => 'DOZENS',
			],
		], json_decode(json_encode($coll), true));
  }

}