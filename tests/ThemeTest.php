<?php

namespace Timber\Tests;

use Mantle\Testing\Attributes\PermalinkStructure;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Tests\Support\Attributes\WithTheme;
use Timber\Theme;
use Timber\Timber;

class ThemeTest extends TimberIntegrationTestCase
{
    #[WithTheme('timber-test-theme')]
    public function testThemeVersion()
    {
        $theme = new Theme();
        $this->assertSame('1.0.1', $theme->version);
    }

    #[WithTheme('timber-test-theme')]
    public function testThemeParentWithNoParent()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{ site.theme.parent.slug }}', $context);
        $this->assertEquals('timber-test-theme', $output);
    }

    public function testThemeMods()
    {
        \set_theme_mod('foo', 'bar');
        $theme = new Theme();
        $mods = $theme->theme_mods();
        $this->assertEquals('bar', $mods['foo']);
        $bar = $theme->theme_mod('foo');
        $this->assertEquals('bar', $bar);
    }

    public function testPath()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.path}}', $context);
        $this->assertEquals('/wp-content/themes/' . $theme->slug, $output);
    }

    #[PermalinkStructure('/%postname%/')]
    #[WithTheme('timber-test-theme')]
    public function testPathWithPort()
    {
        /* setUp */
        \update_option('siteurl', 'http://example.org:3000', true);
        \update_option('home', 'http://example.org:3000', true);
        $old_port = $_SERVER['SERVER_PORT'];
        $_SERVER['SERVER_PORT'] = 3000;
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = 'example.org';
        }

        /* test */
        $theme = new Theme();
        $this->assertEquals('/wp-content/themes/timber-test-theme', $theme->path());

        /* tearDown */
        $_SERVER['SERVER_PORT'] = $old_port;
        \update_option('siteurl', 'http://example.org', true);
        \update_option('home', 'http://example.org', true);
    }

    #[WithOption('siteurl', 'http://example.org/wordpress')]
    public function testPathOnSubdirectoryInstall()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.path}}', $context);
        $this->assertEquals('/wp-content/themes/' . $theme->slug, $output);
    }

    public function testLink()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.link}}', $context);
        $this->assertEquals('http://example.org/wp-content/themes/' . $theme->slug, $output);
    }

    #[WithOption('siteurl', 'http://example.org/wordpress')]
    public function testLinkOnSubdirectoryInstall()
    {
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.link}}', $context);
        $this->assertEquals('http://example.org/wp-content/themes/' . $theme->slug, $output);
    }

    #[WithTheme('timber-test-theme')]
    public function testThemeGet()
    {
        $context = Timber::context();
        $output = Timber::compile_string('{{site.theme.get("Name")}}', $context);
        $this->assertEquals('Timber Tests Theme', $output);
    }

    #[WithTheme('timber-test-theme')]
    public function testThemeDisplay()
    {
        $context = Timber::context();
        $output = Timber::compile_string('{{site.theme.display("Description")}}', $context);
        $this->assertEquals("Parent Theme", $output);
    }

    #[WithTheme('timber-test-theme-child')]
    public function testTimberThemeJsonSerialize()
    {

        $theme = new Theme('timber-test-theme-child');

        $encoded = \json_encode($theme);

        $this->assertNotFalse($encoded);

        $decoded = \json_decode($encoded, true);

        $this->assertEquals([
            'name' => 'Timber Tests Child Theme',
            'parent' => [
                'name' => 'Timber Tests Theme',
                'parent' => null,
                'parent_slug' => null,
                'slug' => 'timber-test-theme',
                'uri' => 'http://example.org/wp-content/themes/timber-test-theme',
                'version' => '1.0.1',
            ],
            'parent_slug' => 'timber-test-theme',
            'slug' => 'timber-test-theme-child',
            'uri' => 'http://example.org/wp-content/themes/timber-test-theme',
            'version' => '1.0.0',
        ], $decoded);
    }
}
