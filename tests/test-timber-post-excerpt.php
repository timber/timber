<?php

use Timber\PostExcerpt;

/**
 * @group called-post-constructor
 */
class TestTimberPostExcerpt extends Timber_UnitTestCase
{
    public function testDoubleEllipsis()
    {
        $post_id = $this->factory->post->create();
        $post = Timber::get_post($post_id);
        $post->post_excerpt = 'this is super dooper trooper long words';
        $excerpt = new PostExcerpt($post, [
            'words' => 3,
            'force' => true,
        ]);

        $this->assertSame(1, substr_count((string) $excerpt, '&hellip;'));
    }

    public function testReadMoreClassFilter()
    {
        $this->add_filter_temporarily('timber/post/excerpt/read_more_class', function ($class) {
            return $class . ' and-foo';
        });
        $post_id = $this->factory->post->create([
            'post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.',
        ]);
        $post = Timber::get_post($post_id);

        $text = new PostExcerpt($post, [
            'words' => 10,
            'always_add_read_more' => true,
        ]);

        $this->assertStringContainsString('and-foo', (string) $text);
    }

    public function testReadMoreLinkFilter()
    {
        $post_id = $this->factory->post->create([
            'post_excerpt' => 'Let this be the excerpt!',
        ]);

        $post = Timber::get_post($post_id);
        $excerpt = $post->excerpt([
            'always_add_read_more' => true,
        ]);

        $this->add_filter_temporarily('timber/post/excerpt/read_more_link', function ($link) {
            return ' Foobar';
        });

        $this->assertEquals('Let this be the excerpt! Foobar', (string) $excerpt);
    }

    /**
     * @expectedDeprecated timber/post/get_preview/read_more_link
     */
    public function testReadMoreLinkFilterDeprecated()
    {
        $post_id = $this->factory->post->create([
            'post_excerpt' => 'Let this be the excerpt!',
        ]);

        $post = Timber::get_post($post_id);
        $excerpt = $post->excerpt([
            'always_add_read_more' => true,
        ]);

        $this->add_filter_temporarily('timber/post/get_preview/read_more_link', function ($link) {
            return ' Foobar';
        });

        $this->assertEquals('Let this be the excerpt! Foobar', (string) $excerpt);
    }

    public function testExcerptTags()
    {
        $post_id = $this->factory->post->create([
            'post_excerpt' => 'It turned out that just about anyone in authority — cops, judges, city leaders — was in on the game.',
        ]);
        $post = Timber::get_post($post_id);
        $text = new PostExcerpt($post, [
            'words' => 20,
            'force' => false,
            'read_more' => '',
            'strip' => false,
        ]);
        $this->assertStringNotContainsString('</p>', (string) $text);
    }

    public function testGetExcerpt()
    {
        global $wp_rewrite;
        $struc = false;
        $wp_rewrite->permalink_structure = $struc;
        update_option('permalink_structure', $struc);
        $post_id = $this->factory->post->create([
            'post_content' => 'this is super dooper trooper long words',
        ]);
        $post = Timber::get_post($post_id);

        // no excerpt
        $post->post_excerpt = '';
        $str = Timber::compile_string('{{ post.excerpt({
            words: 3,
            always_add_read_more: true
        }) }}', [
            'post' => $post,
        ]);
        $this->assertMatchesRegularExpression('/this is super&hellip; <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Read More<\/a>/', $str);

        // excerpt set, force is false, no read more
        $post->post_excerpt = 'this is excerpt longer than three words';
        $excerpt = new PostExcerpt($post, [
            'words' => 3,
            'force' => false,
            'read_more' => '',
        ]);
        $this->assertEquals((string) $excerpt, $post->post_excerpt);

        // custom read more set
        $post->post_excerpt = '';
        $excerpt = new PostExcerpt($post, [
            'words' => 3,
            'force' => false,
            'read_more' => 'Custom more',
            'always_add_read_more' => true,
        ]);
        $this->assertMatchesRegularExpression('/this is super&hellip; <a href="http:\/\/example.org\/\?p=\d+" class="read-more">Custom more<\/a>/', (string) $excerpt);

        // content with <!--more--> tag, force false
        $post->post_content = 'this is super dooper<!--more--> trooper long words';
        $excerpt = new PostExcerpt($post, [
            'words' => 2,
            'force' => false,
            'read_more' => '',
        ]);
        $this->assertEquals('this is super dooper', (string) $excerpt);
    }

    public function testShortcodesInExcerptFromContent()
    {
        add_shortcode('mythang', function ($text) {
            return 'mythangy';
        });
        $pid = $this->factory->post->create([
            'post_content' => 'jared [mythang]',
            'post_excerpt' => '',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals('jared mythangy', $post->excerpt());

        remove_shortcode('mythang');
    }

    public function testShortcodesInExcerptFromContentWithMoreTag()
    {
        add_shortcode('duck', function ($text) {
            return 'Quack!';
        });
        $pid = $this->factory->post->create([
            'post_content' => 'jared [duck] <!--more--> joojoo',
            'post_excerpt' => '',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals(
            sprintf('jared Quack! <a href="%s" class="read-more">Read More</a>', $post->link()),
            (string) $post->excerpt()
        );

        remove_shortcode('duck');
    }

    public function testExcerptWithSpaceInMoreTag()
    {
        $pid = $this->factory->post->create([
            'post_content' => 'Lauren is a duck, but a great duck let me tell you why <!--more--> Lauren is not a duck',
            'post_excerpt' => '',
        ]);
        $post = Timber::get_post($pid);
        $excerpt = new PostExcerpt($post, [
            'words' => 3,
            'force' => true,
        ]);

        $this->assertEquals(
            'Lauren is a&hellip; <a href="' . $post->link() . '" class="read-more">Read More</a>',
            (string) $excerpt
        );
    }

    public function testExcerptWithMoreTagAndForcedLength()
    {
        $pid = $this->factory->post->create([
            'post_content' => 'Lauren is a duck<!-- more--> Lauren is not a duck',
            'post_excerpt' => '',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals(
            sprintf('Lauren is a duck <a href="%s" class="read-more">Read More</a>', $post->link()),
            (string) $post->excerpt()
        );
    }

    public function testExcerptWithCustomMoreTag()
    {
        $pid = $this->factory->post->create([
            'post_content' => 'Eric is a polar bear <!-- more But what is Elaina? --> Lauren is not a duck',
            'post_excerpt' => '',
        ]);
        $post = Timber::get_post($pid);
        $this->assertEquals(
            'Eric is a polar bear <a href="' . $post->link() . '" class="read-more">But what is Elaina?</a>',
            (string) $post->excerpt()
        );
    }

    public function testExcerptWithCustomEnd()
    {
        $pid = $this->factory->post->create([
            'post_content' => 'Lauren is a duck, but a great duck let me tell you why Lauren is a duck',
            'post_excerpt' => '',
        ]);
        $post = Timber::get_post($pid);
        $excerpt = new PostExcerpt($post, [
            'words' => 3,
            'force' => true,
            'read_more' => 'Read More',
            'strip' => true,
            'end' => ' ???',
        ]);
        $this->assertEquals(
            'Lauren is a ??? <a href="' . $post->link() . '" class="read-more">Read More</a>',
            (string) $excerpt
        );
    }

    public function testExcerptWithCustomStripTags()
    {
        $pid = $this->factory->post->create([
            'post_content' => '<span>Even in the <a href="">world</a> of make-believe there have to be rules. The parts have to be consistent and belong together</span>',
        ]);
        $post = Timber::get_post($pid);
        $post->post_excerpt = '';
        $excerpt = new PostExcerpt($post, [
            'words' => 6,
            'force' => true,
            'read_more' => 'Read More',
            'always_add_read_more' => true,
            'strip' => '<span>',
        ]);
        $this->assertEquals(
            '<span>Even in the world of make-believe</span>&hellip; <a href="' . $post->link() . '" class="read-more">Read More</a>',
            (string) $excerpt
        );
    }

    /**
     * When the excerpt is not smaller than the content itself, there should not be a read more
     * link.
     *
     * @ticket #1345
     */
    public function testPostContentWithShorterLengthThanExpectedExcerpt()
    {
        $post_id = $this->factory->post->create([
            'post_content' => 'Let this be the content, albeit a very short one!',
            'post_excerpt' => '',
        ]);

        $post = Timber::get_post($post_id);
        $excerpt = $post->excerpt([
            'always_add_read_more' => false,
        ]);

        $this->assertEquals('Let this be the content, albeit a very short one!', (string) $excerpt);
    }

    /**
     * When the excerpt is not smaller than the content itself, there should not be a read more
     * link.
     *
     * @ticket #1345
     */
    public function testPostContentWithShorterLengthThanExpectedExcerptUsingFilter()
    {
        $this->add_filter_temporarily(
            'timber/post/excerpt/defaults',
            function ($defaults) {
                $defaults['always_add_read_more'] = false;

                return $defaults;
            }
        );

        $post_id = $this->factory->post->create([
            'post_content' => 'Let this be the content, albeit a very short one!',
            'post_excerpt' => '',
        ]);

        $post = Timber::get_post($post_id);
        $excerpt = $post->excerpt();

        $this->assertEquals('Let this be the content, albeit a very short one!', (string) $excerpt);
    }

    /**
     * When always_add_end is used, the end character should be added as well as the as a read
     * more link, even when always_add_read_more is false.
     */
    public function testAlwaysAddEndOption()
    {
        $post_id = $this->factory->post->create([
            'post_content' => 'Let this be the content, albeit a very short one!',
            'post_excerpt' => '',
        ]);

        $post = Timber::get_post($post_id);
        $excerpt = $post->excerpt([
            'always_add_end' => true,
            'always_add_read_more' => false,
        ]);

        $this->assertEquals(sprintf(
            'Let this be the content, albeit a very short one!&hellip;',
            $post->link()
        ), (string) $excerpt);
    }

    /**
     * When always_add_end is used, the end character should be added as well as the as a read
     * more link, even when always_add_read_more is false.
     */
    public function testAlwaysAddEndOptionUsingFilter()
    {
        $this->add_filter_temporarily(
            'timber/post/excerpt/defaults',
            function ($defaults) {
                $defaults['always_add_end'] = true;

                return $defaults;
            }
        );

        $post_id = $this->factory->post->create([
            'post_content' => 'Let this be the content, albeit a very short one!',
            'post_excerpt' => '',
        ]);

        $post = Timber::get_post($post_id);
        $excerpt = $post->excerpt();

        $this->assertEquals('Let this be the content, albeit a very short one!&hellip;', (string) $excerpt);
    }

    /**
     * Checks if the excerpt is correctly generated when the content contains a block.
     * Prior to this fix, the excerpt would cause infinite loops (which show up as a segmentation fault in PHPunit).
     *
     * @ticket https://github.com/timber/timber/issues/2041
     *
     * @return void
     */
    public function testExcerptWithCustomBlock()
    {
        require_once 'assets/block-tests/block-register.php';

        // Create an empty post
        $post_id = $this->factory->post->create([
            'post_excerpt' => '',
            'post_content' => '',
        ]);

        // Update the post with a block and pass the post ID to the block
        $this->factory->post->update_object($post_id, [
            'post_excerpt' => '',
            'post_content' => '<!-- wp:timber/test-block { "post_id": ' . $post_id . '} /--> Some other content',
        ]);

        $post = Timber::get_post($post_id);

        $this->assertEquals('Some other content', (string) $post->excerpt());
    }
}
