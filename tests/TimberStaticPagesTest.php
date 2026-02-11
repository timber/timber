<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Timber;

#[Group('posts-api')]
class TimberStaticPagesTest extends TimberIntegrationTestCase
{
    public function testPageAsPostsPage()
    {
        $pids = static::factory()->post->create_many(6);
        $page_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        $this->setOptionTemporarily('page_for_posts', $page_id);
        $this->get(\home_url('/?page_id=' . $page_id));
        $page = Timber::get_post();
        $this->assertEquals($page_id, $page->ID);
    }

    public function testPageAsJustAPage()
    {
        $pids = static::factory()->post->create_many(6);
        $page_id = static::factory()->post->create([
            'post_title' => 'Foobar',
            'post_name' => 'foobar',
            'post_type' => 'page',
        ]);
        $this->get(\home_url('/?page_id=' . $page_id));
        $page = Timber::get_post();
        $this->assertEquals($page_id, $page->ID);
    }

    public function testPageAsStaticFront()
    {
        $pids = static::factory()->post->create_many(6);
        $page_id = static::factory()->post->create([
            'post_type' => 'page',
        ]);
        $this->setOptionTemporarily('page_on_front', $page_id);
        $this->get(\home_url('/'));
        global $wp_query;
        $wp_query->queried_object = \get_post($page_id);
        $page = Timber::get_post();
        $this->assertEquals($page_id, $page->ID);
    }

    #[WithOption('show_on_front', 'page')]
    public function testFrontPageAsPage()
    {
        $spaceballs = "What's the matter, Colonel Sandurz? Chicken?";
        $page_id = static::factory()->post->create([
            'post_title' => 'Spaceballs',
            'post_content' => $spaceballs,
            'post_type' => 'page',
        ]);
        $this->setOptionTemporarily('page_on_front', $page_id);
        $this->get(\home_url('/'));
        $post = Timber::get_post();
        $this->assertEquals($page_id, $post->ID);
    }

    #[WithOption('show_on_front', 'page')]
    public function testStaticPostPage()
    {
        $page_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
            'post_type' => 'page',
        ]);
        $posts = static::factory()->post->create_many(10, [
            'post_title' => 'Timmy',
        ]);

        $this->setOptionTemporarily('page_for_posts', $page_id);
        $this->get(\get_permalink($page_id));

        $posts = Timber::get_posts();

        $this->assertEquals('Timmy', $posts[0]->title());
    }

    public function testOtherPostOnStaticPostPage()
    {
        $page_id = static::factory()->post->create([
            'post_title' => 'Gobbles',
            'post_type' => 'page',
        ]);
        $this->setOptionTemporarily('page_for_posts', $page_id);
        $post_id = static::factory()->post->create([
            'post_title' => 'My Real post',
            'post_type' => 'post',
        ]);
        $this->get(\home_url('/?p=' . $page_id));

        $post = Timber::get_post($post_id);
        $this->assertEquals($post_id, $post->ID);
    }

    public function testRegularStaticPage(): never
    {
        $this->markTestSkipped('@todo what is this testing?');

        $page_id = static::factory()->post->create([
            'post_title' => 'Mister Slave',
            'post_type' => 'page',
        ]);
        $children = static::factory()->post->create_many(10, [
            'post_title' => 'Timmy',
        ]);
        $this->get(\home_url('/?p=' . $page_id));

        $posts = Timber::get_posts();
        $this->assertCount(0, $posts);

        $page = Timber::get_post();
        $this->assertEquals($page_id, $page->ID);
    }

    public function testRegularStaticPageFlipped(): never
    {
        $this->markTestSkipped('@todo what is this testing?');

        $page_id = static::factory()->post->create([
            'post_title' => 'Mister Slave',
            'post_type' => 'page',
        ]);
        $children = static::factory()->post->create_many(10, [
            'post_title' => 'Timmy',
        ]);
        $this->get(\home_url('/?p=' . $page_id));

        $page = Timber::get_post();
        $this->assertEquals($page_id, $page->ID);

        $posts = Timber::get_posts();
        $this->assertCount(0, $posts);
    }
}
