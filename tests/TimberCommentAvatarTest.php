<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('comments-api')]
class TimberCommentAvatarTest extends TimberIntegrationTestCase
{
    /**
     * Check if we have internet connectivity.
     */
    protected function isConnected(): bool
    {
        $connected = @\fsockopen('www.google.com', 80, $errno, $errstr, 3);
        if ($connected) {
            \fclose($connected);
            return true;
        }
        return false;
    }

    public function testAvatarSize()
    {
        if (!$this->isConnected()) {
            $this->markTestSkipped('Cannot test avatar images when not connected to internet');
        }

        $post_id = static::factory()->post->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $comment = Timber::get_comment($comment_id);

        # test default gravatr holding image
        $avatar = $comment->avatar("mystery");

        $this->assertTrue(\str_starts_with((string) $avatar, "https:"));
    }

    public function testAvatarFalse()
    {
        \update_option('show_avatars', false);
        $post_id = static::factory()->post->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $comment = Timber::get_comment($comment_id);

        # test default gravatr holding image
        $avatar = $comment->avatar();

        $this->assertFalse($avatar);
    }

    public function testAvatarBlank()
    {
        if (!$this->isConnected()) {
            $this->markTestSkipped('Cannot test avatar images when not connected to internet');
        }
        $post_id = static::factory()->post->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $comment = Timber::get_comment($comment_id);

        # test default gravatr holding image
        $avatar = $comment->avatar(92, "blank");

        $this->assertTrue(\str_starts_with((string) $avatar, "http:"));
    }

    public function testAvatarGravatarDefault()
    {
        if (!$this->isConnected()) {
            $this->markTestSkipped('Cannot test avatar images when not connected to internet');
        }
        $post_id = static::factory()->post->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $comment = Timber::get_comment($comment_id);

        # test default gravatr holding image
        $avatar = $comment->avatar(92, "gravatar_default");

        $this->assertTrue(\str_starts_with((string) $avatar, "https:"));
    }

    public function testGravatar()
    {
        if (!$this->isConnected()) {
            $this->markTestSkipped('Cannot test avatar images when not connected to internet');
        }
        $post_id = static::factory()->post->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_author' => 'jarednova',
            'comment_author_email' => 'jarednova@upstatement.com',
        ]);
        $comment = Timber::get_comment($comment_id);
        $gravatar = \md5(\file_get_contents($comment->avatar()));
        /* this keeps changing b/c of compression tweaks on WP.org, disabling the test */
        //$this->assertEquals($gravatar, md5(file_get_contents(dirname(__FILE__).'/Fixtures/assets/jarednova.jpeg')));

        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
            'comment_author' => 'jarednova',
            'comment_author_email' => 'notjared@upstatement.com',
        ]);
        $comment = Timber::get_comment($comment_id);
        $not_gravatar = \md5(\file_get_contents($comment->avatar()));
        $this->assertNotEquals($not_gravatar, \md5(\file_get_contents($this->getFixtureAsset('jarednova.jpeg'))));
    }

    public function testAvatarSimple()
    {
        if (!$this->isConnected()) {
            $this->markTestSkipped('Cannot test avatar images when not connected to internet');
        }
        $theme_url = \get_theme_root_uri() . '/' . \get_stylesheet();
        $post_id = static::factory()->post->create();
        $comment_id = static::factory()->comment->create([
            'comment_post_ID' => $post_id,
        ]);
        $comment = Timber::get_comment($comment_id);

        # test default gravatr holding image
        $avatar = $comment->avatar(32, "mystery");

        $this->assertTrue(\str_starts_with((string) $avatar, "https:"));

        # does it work if its SSL?
        $_SERVER['HTTPS'] = 'on';
        $avatar = $comment->avatar(32, "mystery");
        $this->assertTrue(200 === $this->crawl($avatar));
        $this->assertTrue(\str_starts_with((string) $avatar, "https:"));
        $_SERVER['HTTPS'] = 'off';

        # pass custom url on different domain. can't check by crawling as
        # i get a 302 regardless of default url
        # so just check it comes back with it in the url
        $this->valid_avatar($comment, 'https://upload.wikimedia.org/wikipedia/en/b/bc/Wiki.png');

        # same domain.
        $this->valid_avatar($comment, $theme_url . "/images/default.png");

        $default_url = \get_stylesheet_directory_uri() . "/images/default.png";
        $avatar = $comment->avatar(32, $default_url);
        if (\strstr((string) $avatar, '?')) {
            [$url, $params] = \explode('?', (string) $avatar);
            # you get back the absolute url to default in the avatar url?
            $this->assertEquals($params, "d=$default_url&amp;s=32");
        }
        # you get back url?
        $this->assertTrue(\str_starts_with($theme_url . $avatar, "http:"));
    }

    public function valid_avatar($comment, $default_url)
    {
        $avatar = $comment->avatar(32, $default_url);
        if (\strstr((string) $avatar, '?')) {
            [$url, $params] = \explode('?', (string) $avatar);
            # you get back the default in the avatar url?
            $this->assertEquals($params, "d=$default_url&amp;s=32");
        }

        // Check if we get back an URL (either http:// or https:).
        $this->assertMatchesRegularExpression("/^https?:.*/", $avatar);
    }

    private function crawl($url)
    {
        $handle = \curl_init($url);
        \curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        /* Get the HTML or whatever is linked in $url. */
        $response = \curl_exec($handle);
        /* Check for 404 (file not found). */
        $httpCode = \curl_getinfo($handle, CURLINFO_HTTP_CODE);
        \curl_close($handle);
        return $httpCode;
    }
}
