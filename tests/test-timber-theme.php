<?php

use Timber\Theme;

class TestTimberTheme extends Timber_UnitTestCase
{
    public $theme_slug = 'timber-test-theme';

    public function set_up()
    {
        parent::set_up();

        $this->clean_themes_cache();

        $theme = wp_get_theme($this->theme_slug);
        if (!$theme->exists()) {
            $this->markTestSkipped('The ' . $this->theme_slug . ' theme is not available');
        }
    }

    public function tear_down()
    {
        $this->restore_themes();
        switch_theme('default');

        parent::tear_down();
    }

    public function testThemeVersion()
    {
        switch_theme($this->theme_slug);
        $theme = new Theme();
        $this->assertSame('1.0.1', $theme->version);
    }

    public function testThemeParentWithNoParent()
    {
        switch_theme($this->theme_slug);
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{ site.theme.parent.slug }}', $context);
        $this->assertEquals('timber-test-theme', $output);
    }

    public function testThemeMods()
    {
        set_theme_mod('foo', 'bar');
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

    public function testPathWithPort()
    {
        switch_theme($this->theme_slug);

        /* setUp */
        update_option('siteurl', 'http://example.org:3000', true);
        update_option('home', 'http://example.org:3000', true);
        self::setPermalinkStructure();
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
        update_option('siteurl', 'http://example.org', true);
        update_option('home', 'http://example.org', true);
    }

    public function testPathOnSubdirectoryInstall()
    {
        update_option('siteurl', 'http://example.org/wordpress', true);
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

    public function testLinkOnSubdirectoryInstall()
    {
        update_option('siteurl', 'http://example.org/wordpress', true);
        $context = Timber::context();
        $theme = $context['site']->theme;
        $output = Timber::compile_string('{{site.theme.link}}', $context);
        $this->assertEquals('http://example.org/wp-content/themes/' . $theme->slug, $output);
    }

    public function testThemeGet()
    {
        switch_theme($this->theme_slug);
        $context = Timber::context();
        $output = Timber::compile_string('{{site.theme.get("Name")}}', $context);
        $this->assertEquals('Timber Tests Theme', $output);
    }

    public function testThemeDisplay()
    {
        switch_theme($this->theme_slug);
        $context = Timber::context();
        $output = Timber::compile_string('{{site.theme.display("Description")}}', $context);
        $this->assertEquals("Parent Theme", $output);
    }

    public function testTimberThemeJsonSerialize()
    {
        switch_theme('timber-test-theme-child');

        $theme = new Theme('timber-test-theme-child');

        $encoded = json_encode($theme);

        $this->assertNotFalse($encoded);

        $decoded = json_decode($encoded, true);

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
