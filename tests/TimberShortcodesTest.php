<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Ticket;
use Timber\Timber;

class TimberShortcodesTest extends TimberIntegrationTestCase
{
    public function testShortcodes()
    {
        \add_shortcode('timber_shortcode', fn ($text) => 'timber ' . $text[0]);
        $return = Timber::compile('assets/test-shortcodes.twig');
        $this->assertEquals('hello timber foo', \trim($return));
    }

    #[Ticket('#2268')]
    public function testCustomFieldShortcode()
    {
        \add_shortcode('foobar', fn ($atts) => 'barfoo');

        $post_id = static::factory()->post->create();
        \update_post_meta($post_id, 'customfield', '[foobar]');
        $template = '{{ post.customfield | shortcodes }}';

        $post = Timber::get_post($post_id);
        $compiled = Timber::compile_string($template, [
            'post' => $post,
        ]);

        $this->assertEquals('barfoo', $compiled);
    }
}
