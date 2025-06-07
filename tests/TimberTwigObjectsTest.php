<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

#[Group('posts-api')]
#[Group('terms-api')]
#[Group('users-api')]
#[Group('comments-api')]
#[Group('twig')]
#[Group('attachments')]
class TimberTwigObjectsTest extends TimberIntegrationTestCase
{
    public function testImageWithGetPostInTwig()
    {
        $compiled = Timber::compile_string('{{ get_post(iid).src }}', [
            'iid' => $this->createAttachmentWithImage(0, 'arch.jpg'),
        ]);

        $pattern = '#^http://example\.org/wp-content/uploads/\d{4}/\d{2}/arch(-\d+)?\.jpg$#';
        $this->assertMatchesRegularExpression($pattern, $compiled);
    }

    public function testImageWithGetImageInTwig()
    {
        $compiled = Timber::compile_string('{{ get_image(iid).src }}', [
            'iid' => $this->createAttachmentWithImage(0, 'arch.jpg'),
        ]);

        $pattern = '#^http://example\.org/wp-content/uploads/\d{4}/\d{2}/arch(-\d+)?\.jpg$#';
        $this->assertMatchesRegularExpression($pattern, $compiled);
    }

    public function testExternalImageWithGetExternalImageInTwig()
    {
        \switch_theme('twentytwentyone');

        $dest = Image\ExternalImageTest::copy_image_to_stylesheet('assets/images');
        $this->assertFileExists($dest);

        $compiled = Timber::compile_string('{{ get_external_image(image_path).src }}', [
            'image_path' => $dest,
        ]);

        $this->assertStringContainsString('wp-content/themes/twentytwentyone/assets/images/cardinals', $compiled);

        \switch_theme('default');
    }

    public function testImagesWithGetPostsInTwig()
    {
        $images = [];
        $images[] = $this->createAttachmentWithImage(0, 'arch.jpg');
        $images[] = $this->createAttachmentWithImage(0, 'city-museum.jpg');
        $str = '{% for image in get_posts(images) %}{{ image.src }}{% endfor %}';
        $compiled = Timber::compile_string($str, [
            'images' => $images,
        ]);
        $this->assertStringContainsString('arch', $compiled);
        $this->assertStringContainsString('city-museum', $compiled);
        $this->assertStringContainsString('wp-content/uploads/', $compiled);
    }

    public function testTimberImageInTwigToString()
    {
        $compiled = Timber::compile_string('{{ get_post(iid) }}', [
            'iid' => $this->createAttachmentWithImage(0, 'arch.jpg'),
        ]);

        $pattern = '#^http://example\.org/wp-content/uploads/\d{4}/\d{2}/arch(-\d+)?\.jpg$#';
        $this->assertMatchesRegularExpression($pattern, $compiled);
    }

    public function testTimberImageWithGetPostInTwigToString()
    {
        $iid = $this->createAttachmentWithImage(0, 'arch.jpg');
        $str = '{{ get_post(' . $iid . ') }}';
        $compiled = Timber::compile_string($str);
        $pattern = '#^http://example\.org/wp-content/uploads/\d{4}/\d{2}/arch(-\d+)?\.jpg$#';
        $this->assertMatchesRegularExpression($pattern, $compiled);
    }

    public function testGetPostInTwig()
    {
        $pid = static::factory()->post->create([
            'post_title' => 'Foo',
        ]);
        $this->assertEquals('Foo', Timber::compile_string('{{ get_post(pid).title }}', [
            'pid' => $pid,
        ]));
    }

    public function testGetPostsInTwig()
    {
        $pids[] = static::factory()->post->create([
            'post_title' => 'Foo',
        ]);
        $pids[] = static::factory()->post->create([
            'post_title' => 'Bar',
        ]);
        $str = '{% for post in get_posts(pids) %}{{post.title}}{% endfor %}';
        $this->assertEquals('FooBar', Timber::compile_string($str, [
            'pids' => $pids,
        ]));
    }

    public function testGetPostsWithQueryStringInTwig()
    {
        $this->setExpectedIncorrectUsage('Timber::get_posts()');
        $pids[] = static::factory()->post->create([
            'post_title' => 'Foo',
        ]);
        $pids[] = static::factory()->post->create([
            'post_title' => 'Bar',
        ]);
        $str = "{% for post in get_posts('post_type=post&posts_per_page=-1&order=ASC') %}{{ post.title }}{% endfor %}";

        $this->assertEquals('FooBar', Timber::compile_string($str, [
            'pids' => $pids,
        ]));
    }

    public function testGetPostsWithArgsInTwig()
    {
        $pids[] = static::factory()->post->create([
            'post_title' => 'Foo',
        ]);
        $pids[] = static::factory()->post->create([
            'post_title' => 'Bar',
        ]);
        $str = "{% for post in get_posts({ post_type: 'post', posts_per_page: -1, order: 'ASC'}) %}{{ post.title }}{% endfor %}";

        $this->assertEquals('FooBar', Timber::compile_string($str, [
            'pids' => $pids,
        ]));
    }

    public function testGetUsersInTwig()
    {
        $uids[] = static::factory()->user->create([
            'display_name' => 'Mark Watabe',
        ]);
        $uids[] = static::factory()->user->create([
            'display_name' => 'Austin Tzou',
        ]);
        $str = '{% for user in get_users(uids) %}{{ user.name }} {% endfor %}';
        $this->assertEquals(
            'Mark Watabe Austin Tzou',
            \trim(Timber::compile_string($str, [
                'uids' => $uids,
            ]))
        );
    }

    public function testGetUserInTwig()
    {
        $uid = static::factory()->user->create([
            'display_name' => 'Nathan Hass',
        ]);
        $str = '{{ get_user(' . $uid . ').name }}';
        $this->assertEquals('Nathan Hass', Timber::compile_string($str));
    }

    public function testGetTermInTwig()
    {
        $tid = static::factory()->term->create([
            'name' => 'Mythbusters',
        ]);
        $str = '{{ get_term(tid).title }}';
        $this->assertEquals('Mythbusters', Timber::compile_string($str, [
            'tid' => $tid,
        ]));
    }

    public function testGetTermsInTwig()
    {
        $tids[] = static::factory()->term->create([
            'name' => 'Animals',
        ]);
        $tids[] = static::factory()->term->create([
            'name' => 'Germans',
        ]);
        $str = '{% for term in get_terms(tids) %}{{term.title}} {% endfor %}';
        $this->assertEquals('Animals Germans ', Timber::compile_string($str, [
            'tids' => $tids,
        ]));
    }
}
