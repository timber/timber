<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

/**
 * Ensures that autosave previews in `Timber::handle_preview()` are only applied for users who
 * are allowed to edit the post and who send a valid preview nonce.
 *
 * @see https://github.com/timber/timber/security/advisories/GHSA-53wf-g7j4-hwhh
 */
#[Group('posts-api')]
class TimberPreviewSecurityTest extends TimberIntegrationTestCase
{
    public function tear_down()
    {
        unset($_GET['preview'], $_GET['preview_id'], $_GET['preview_nonce']);
        parent::tear_down();
    }

    private function make_post_with_autosave($author_id, $autosave_user_id = null)
    {
        \wp_set_current_user($author_id);
        $post_id = static::factory()->post->create([
            'post_author' => $author_id,
            'post_title' => 'Published Title',
            'post_content' => 'Published content',
            'post_excerpt' => 'Published excerpt',
        ]);
        \wp_set_current_user($autosave_user_id ?? $author_id);
        \_wp_put_post_revision([
            'ID' => $post_id,
            'post_title' => 'Secret Title',
            'post_content' => 'Secret content',
            'post_excerpt' => 'Secret excerpt',
        ], true);
        return $post_id;
    }

    private function request($post_id, $nonce_for = null, $nonce = null)
    {
        $_GET['preview'] = 'true';
        $_GET['preview_id'] = (string) $post_id;
        if ($nonce !== null) {
            $_GET['preview_nonce'] = $nonce;
        } elseif ($nonce_for !== null) {
            $_GET['preview_nonce'] = \wp_create_nonce('post_preview_' . $nonce_for);
        }
    }

    private function editor()
    {
        return static::factory()->user->create([
            'role' => 'editor',
        ]);
    }

    public function testAnonymousNoNonce()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        \wp_set_current_user(0);
        $this->request($post_id);
        $post = Timber::get_post($post_id);
        $this->assertSame('Published Title', $post->post_title);
        $this->assertSame('Published content', $post->post_content);
        $this->assertSame('Published excerpt', $post->post_excerpt);
    }

    public function testAnonymousWithLoggedOutNonce()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        \wp_set_current_user(0);
        $this->request($post_id, $post_id);
        $this->assertSame('Published Title', Timber::get_post($post_id)->post_title);
    }

    public function testAnonymousListingSwap()
    {
        $editor = $this->editor();
        $target = $this->make_post_with_autosave($editor);
        static::factory()->post->create_many(2);
        \wp_set_current_user(0);
        $this->request($target);
        $titles = \array_map(fn ($p) => $p->post_title, Timber::get_posts([
            'posts_per_page' => -1,
        ])->to_array());
        $this->assertNotContains('Secret Title', $titles);
    }

    public function testSubscriberWithOwnNonce()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'subscriber',
        ]));
        $this->request($post_id, $post_id);
        $this->assertSame('Published Title', Timber::get_post($post_id)->post_title);
    }

    public function testContributorCannotPreviewOthersPublishedPost()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        \wp_set_current_user(static::factory()->user->create([
            'role' => 'contributor',
        ]));
        $this->request($post_id, $post_id);
        $this->assertSame('Published Title', Timber::get_post($post_id)->post_title);
    }

    public function testEditorWithoutNonce()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        $this->request($post_id);
        $this->assertSame('Published Title', Timber::get_post($post_id)->post_title);
    }

    public function testEditorWithNonceForOtherPost()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        $this->request($post_id, $post_id + 999);
        $this->assertSame('Published Title', Timber::get_post($post_id)->post_title);
    }

    public function testEditorWithArrayNonce()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        $this->request($post_id, null, ['x']);
        $this->assertSame('Published Title', Timber::get_post($post_id)->post_title);
    }

    public function testEditorValidPreview()
    {
        $post_id = $this->make_post_with_autosave($this->editor());
        $this->request($post_id, $post_id);
        $post = Timber::get_post($post_id);
        $this->assertSame('Secret Title', $post->post_title);
        $this->assertSame('Secret content', $post->post_content);
        $this->assertSame('Secret excerpt', $post->post_excerpt);
    }

    public function testAuthorPreviewsOwnPost()
    {
        $author = static::factory()->user->create([
            'role' => 'author',
        ]);
        $post_id = $this->make_post_with_autosave($author);
        $this->request($post_id, $post_id);
        $this->assertSame('Secret Title', Timber::get_post($post_id)->post_title);
    }

    public function testEditorPreviewOnlyOtherUsersAutosave()
    {
        // Like core's _set_preview(), use the latest autosave regardless of which user created it.
        $post_id = $this->make_post_with_autosave($this->editor());
        \wp_set_current_user($this->editor());
        $this->request($post_id, $post_id);
        $this->assertSame('Secret Title', Timber::get_post($post_id)->post_title);
    }

    public function testOtherPostsInListingUntouched()
    {
        $editor = $this->editor();
        $target = $this->make_post_with_autosave($editor);
        $other = static::factory()->post->create([
            'post_title' => 'Other',
        ]);
        $this->request($target, $target);
        $this->assertSame('Other', Timber::get_post($other)->post_title);
        $this->assertSame('Secret Title', Timber::get_post($target)->post_title);
    }
}
