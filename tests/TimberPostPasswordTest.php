<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('called-post-constructor')]
class TimberPostPasswordTest extends TimberIntegrationTestCase
{
    public function testPasswordedContentDefault()
    {
        $quote = 'The way to do well is to do well.';
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        $post->post_password = 'burrito';
        \wp_update_post($post);
        $password_form = \get_the_password_form($post->ID);
        $this->assertEquals(\wpautop($quote), $post->content());
    }

    public function testPasswordedContentWhenEnabled()
    {
        \add_filter('timber/post/content/show_password_form_for_protected', fn ($maybe_show) => true);
        $quote = 'The way to do well is to do well.';
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        $post->post_password = 'burrito';
        \wp_update_post($post);
        $password_form = \get_the_password_form($post->ID);
        $this->assertEquals($password_form, $post->content());
    }

    public function testPasswordedContentWhenEnabledWithCustomForm()
    {
        \add_filter('timber/post/content/show_password_form_for_protected', fn ($maybe_show) => true);
        \add_filter('timber/post/content/password_form', fn ($form, $post) => Timber::compile('assets/password-form.twig', [
            'post' => $post,
        ]), 10, 2);
        $quote = 'The way to do well is to do well.';
        $post_id = static::factory()->post->create([
            'post_title' => 'Secrets!',
        ]);
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        $post->post_password = 'burrito';
        \wp_update_post($post);
        $password_form = '<form>Enter password to see Secrets!</form>';
        $this->assertEquals($password_form, $post->content());
    }
}
