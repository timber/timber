<?php

/**
 * @group called-post-constructor
 */
class TestTimberPostPassword extends Timber_UnitTestCase
{
    public function testPasswordedContentDefault()
    {
        $quote = 'The way to do well is to do well.';
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        $post->post_password = 'burrito';
        wp_update_post($post);
        $password_form = get_the_password_form($post->ID);
        $this->assertEquals(wpautop($quote), $post->content());
    }

    public function testPasswordedContentWhenEnabled()
    {
        add_filter('timber/post/content/show_password_form_for_protected', function ($maybe_show) {
            return true;
        });
        $quote = 'The way to do well is to do well.';
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        $post->post_password = 'burrito';
        wp_update_post($post);
        $password_form = get_the_password_form($post->ID);
        $this->assertEquals($password_form, $post->content());
    }

    public function testPasswordedContentWhenEnabledWithCustomForm()
    {
        add_filter('timber/post/content/show_password_form_for_protected', function ($maybe_show) {
            return true;
        });
        add_filter('timber/post/content/password_form', function ($form, $post) {
            return Timber::compile('assets/password-form.twig', [
                'post' => $post,
            ]);
        }, 10, 2);
        $quote = 'The way to do well is to do well.';
        $post_id = $this->factory->post->create([
            'post_title' => 'Secrets!',
        ]);
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        $post->post_password = 'burrito';
        wp_update_post($post);
        $password_form = '<form>Enter password to see Secrets!</form>';
        $this->assertEquals($password_form, $post->content());
    }
}
