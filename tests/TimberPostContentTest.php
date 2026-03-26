<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Ticket;
use Timber\Timber;

#[Group('posts-api')]
class TimberPostContentTest extends TimberIntegrationTestCase
{
    public function testContent()
    {
        $quote = 'The way to do well is to do well.';
        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        \wp_update_post($post);
        $this->assertEquals($quote, \trim(\strip_tags($post->content())));
    }

    public function testContentPaged()
    {
        $quote = $page1 = 'The way to do well is to do well.';
        $quote .= '<!--nextpage-->';
        $quote .= $page2 = "And do not let your tongue get ahead of your mind.";

        $post_id = static::factory()->post->create();
        $post = Timber::get_post($post_id);
        $post->post_content = $quote;
        \wp_update_post($post);

        $this->assertEquals($page1, \trim(\strip_tags($post->content(1))));
        $this->assertEquals($page2, \trim(\strip_tags($post->content(2))));
    }

    public function testPagedContent()
    {
        $quote = $page1 = 'Named must your fear be before banish it you can.';
        $quote .= '<!--nextpage-->';
        $quote .= $page2 = "No, try not. Do or do not. There is no try.";

        $post_id = static::factory()->post->create([
            'post_content' => $quote,
        ]);

        $this->get(\get_permalink($post_id));

        \setup_postdata(\get_post($post_id));

        $post = Timber::get_post();
        $this->assertEquals($page1, \trim(\strip_tags($post->paged_content())));

        $pagination = $post->pagination();
        $this->get((string) $pagination['pages'][1]['link']);

        \setup_postdata(\get_post($post_id));
        $post = Timber::get_post();

        $this->assertEquals($page2, \trim(\strip_tags($post->paged_content())));
    }

    public function testPagedContentWithBlocks()
    {
        $paged_content =
            /** @lang text */
            '<!-- wp:group -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p>Paged Content</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:nextpage -->
<!--nextpage-->
<!-- /wp:nextpage -->

<!-- wp:group -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p>Paged Content</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';

        $post_id = static::factory()->post->create([
            'post_title' => 'Paged content',
            'post_content' => $paged_content,
            'post_type' => 'post',
        ]);

        $this->get(\get_permalink($post_id));
        \setup_postdata(\get_post($post_id));

        $post = Timber::get_post();
        $post->setup();

        $paged_content = \trim(\do_blocks(
            /** @lang text */
            '<!-- wp:group -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p>Paged Content</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->'
        ));

        $this->assertEquals($paged_content, \trim($post->paged_content()));

        // Go to next page.
        $pagination = $post->pagination();
        $this->get((string) $pagination['pages'][1]['link']);
        \setup_postdata(\get_post($post_id));

        $post = Timber::get_post();
        $post->setup();

        $this->assertEquals($paged_content, \trim($post->paged_content()));
    }

    public function testPagedContentWithBlocksAndNextPageAtBeginning()
    {
        $paged_content =
            /** @lang text */
            '<!-- wp:nextpage -->
<!--nextpage-->
<!-- /wp:nextpage -->

<!-- wp:group -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p>Paged Content</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:nextpage -->
<!--nextpage-->
<!-- /wp:nextpage -->

<!-- wp:group -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p>Paged Content</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->';

        $post_id = static::factory()->post->create([
            'post_title' => 'Paged content',
            'post_content' => $paged_content,
            'post_type' => 'post',
        ]);

        $this->get(\get_permalink($post_id));
        \setup_postdata(\get_post($post_id));

        $post = Timber::get_post();
        $post->setup();

        $paged_content = \trim(\do_blocks(
            /** @lang text */
            '<!-- wp:group -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p>Paged Content</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->'
        ));

        $this->assertEquals($paged_content, \trim($post->paged_content()));

        // Go to next page.
        $pagination = $post->pagination();
        $this->get((string) $pagination['pages'][1]['link']);
        \setup_postdata(\get_post($post_id));

        $post = Timber::get_post();
        $post->setup();

        $this->assertEquals($paged_content, \trim($post->paged_content()));
    }

    #[Ticket('2218')]
    public function testGutenbergExcerptOption()
    {
        $content_1 = '<!-- wp:paragraph -->
<p>Heres the start to a thing</p>
<!-- /wp:paragraph -->

<!-- wp:more {"noTeaser":true} -->
<!--more-->
<!--noteaser-->
<!-- /wp:more -->

<!-- wp:paragraph -->
<p>Heres the read more stuff that we shant see!</p>
<!-- /wp:paragraph -->';
        $post_id = static::factory()->post->create([
            'post_content' => $content_1,
        ]);
        $post = Timber::get_post($post_id);

        $this->assertStringEndsWith('Heres the read more stuff that we shant see!</p>', \trim($post->content()));
    }

    #[Ticket('3208')]
    public function testExcerptDoesNotPoisonContentCache()
    {
        // Create post content that will be affected by excerpt_remove_blocks
        // We'll use actual Gutenberg block syntax with dynamic blocks
        $content = '<!-- wp:paragraph -->
<p>Introduction paragraph</p>
<!-- /wp:paragraph -->

<!-- wp:latest-posts {"postsToShow":3} /-->

<!-- wp:paragraph -->
<p>Conclusion paragraph</p>
<!-- /wp:paragraph -->';

        $post_id = static::factory()->post->create([
            'post_content' => $content,
        ]);
        $post = Timber::get_post($post_id);

        // Get the content with remove_blocks = true (what excerpt() does internally)
        // This simulates what happens when excerpt() is called
        $content_with_blocks_removed = $post->content(0, -1, true);

        // Verify that the latest-posts block was removed from the excerpt version
        // After the_content filter, this renders as wp-block-latest-posts HTML
        $this->assertStringNotContainsString('wp-block-latest-posts', $content_with_blocks_removed);

        // Now get the normal content (with remove_blocks = false, the default)
        // This is where the bug manifests: if the cache was poisoned,
        // it will return the content without the latest-posts block
        $full_content = $post->content();

        // The full content should contain the rendered latest-posts block
        // because we're calling content() with remove_blocks = false
        // This will FAIL if the cache was poisoned by the previous call
        $this->assertStringContainsString('wp-block-latest-posts', $full_content);
    }

    #[Ticket('3208')]
    public function testExcerptThenContentRealWorld()
    {
        // This test simulates the real-world scenario described in issue #3208
        // where excerpt() is called before content() in templates
        $content = '<!-- wp:paragraph -->
<p>This is the introduction to my post</p>
<!-- /wp:paragraph -->

<!-- wp:latest-posts {"postsToShow":5} /-->

<!-- wp:paragraph -->
<p>And this is the conclusion</p>
<!-- /wp:paragraph -->';

        $post_id = static::factory()->post->create([
            'post_content' => $content,
        ]);
        $post = Timber::get_post($post_id);

        // Call excerpt() first (as would happen in templates/Schema generation)
        $excerpt = $post->excerpt();

        // The excerpt should NOT contain the dynamic block
        $this->assertStringNotContainsString('wp-block-latest-posts', $excerpt);

        // Now call content() (as would happen when rendering the full post)
        $full_content = $post->content();

        // The content MUST contain the dynamic block
        // Before the fix, this would fail because the cache was poisoned
        $this->assertStringContainsString('wp-block-latest-posts', $full_content);
        $this->assertStringContainsString('This is the introduction', $full_content);
        $this->assertStringContainsString('And this is the conclusion', $full_content);
    }
}
