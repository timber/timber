<?php

namespace Timber\Tests;

use AllowDynamicProperties;
use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Timber\Image;
use Timber\Site;
use Timber\Timber;
use Timber\URLHelper;

#[AllowDynamicProperties]
class SiteTest extends TimberIntegrationTestCase
{
    public function testStandardThemeLocation()
    {
        \switch_theme('timber-test-theme');

        $site = new Site();
        $content_subdir = URLHelper::get_content_subdir();
        $this->assertEquals($content_subdir . '/themes/timber-test-theme', $site->theme->path);

        \switch_theme('default');
    }

    public function testLanguageAttributes()
    {
        \restore_current_locale();
        $site = new Site();
        $lang = $site->language_attributes();
        $this->assertEquals('lang="en-US"', $lang);
    }

    public function testChildParentThemeLocation()
    {
        $content_subdir = URLHelper::get_content_subdir();
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/timber-test-theme-child/style.css');

        \switch_theme('timber-test-theme-child');
        $site = new Site();
        $this->assertEquals($content_subdir . '/themes/timber-test-theme-child', $site->theme->path);
        $this->assertEquals($content_subdir . '/themes/timber-test-theme', $site->theme->parent->path);

        \switch_theme('default');
    }

    public function testThemeFromContext()
    {
        \switch_theme('timber-test-theme');

        $context = Timber::context();
        $this->assertEquals('timber-test-theme', $context['theme']->slug);

        \switch_theme('default');
    }

    public function testThemeFromSiteContext()
    {
        \switch_theme('timber-test-theme');

        $context = Timber::context();
        $this->assertEquals('timber-test-theme', $context['site']->theme->slug);

        \switch_theme('default');
    }

    public function testSiteURL()
    {
        $site = new Site();
        $this->assertEquals('http://example.org', $site->link());
        $this->assertEquals(\site_url(), $site->site_url);
    }

    public function testHomeUrl()
    {
        $site = new Site();
        $this->assertEquals($site->url, $site->home_url);
    }

    public function testSiteIcon()
    {
        $icon_id = static::factory()->attachment->with_image($this->getFixtureAsset('cardinals.jpg'))->create();
        \update_option('site_icon', $icon_id);
        $site = new Site();
        $icon = $site->icon();

        $this->assertEquals(Image::class, $icon !== null ? $icon::class : self::class);
        $this->assertStringContainsString(\wp_get_attachment_image_src($icon_id, 'full')[0], $icon->src());
    }

    public function testNullIcon()
    {
        \delete_option('site_icon');
        $site = new Site();
        $this->assertNull($site->icon());
    }

    public function testSiteGet()
    {
        \update_option('foo', 'bar');
        $site = new Site();
        $this->assertEquals('bar', $site->foo);
    }

    public function testSiteCall()
    {
        \update_option('foo', 'barr');
        $site = new Site();

        $twig_string = '{{site.foo}}';
        $result = Timber::compile_string($twig_string, [
            'site' => $site,
        ]);
        $this->assertEquals('barr', $result);
    }

    #[IgnoreDeprecations]
    public function testSiteMeta()
    {
        $this->setExpectedDeprecated('{{ site.meta() }}');
        $ts = new Site();
        \update_option('foo', 'magoo');
        $this->assertEquals('magoo', $ts->meta('foo'));
    }

    public function testSiteOption()
    {
        $ts = new Site();
        \update_option('date_format', 'j. F Y');
        $this->assertEquals('j. F Y', $ts->option('date_format'));
    }

    public function testWPObject()
    {
        $this->skipWithMultisite();

        $ts = new Site();
        $this->assertNull($ts->wp_object());
    }

    public function set_up()
    {
        parent::set_up();
        $this->clean_themes_cache();
    }

    public function tear_down()
    {
        $this->restore_themes();
        parent::tear_down();
    }
}
