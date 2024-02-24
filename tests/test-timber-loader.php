<?php

use Twig\Loader\LoaderInterface;

class TestTimberLoader extends Timber_UnitTestCase
{
    public function testTwigLoaderFilter()
    {
        $php_unit = $this;
        add_filter('timber/loader/loader', function ($loader) use ($php_unit) {
            $php_unit->assertInstanceOf(LoaderInterface::class, $loader);
            return $loader;
        });
        $str = Timber::compile('assets/single.twig', []);
    }

    public function testBogusTemplate()
    {
        $str = Timber::compile('assets/darkhelmet.twig');
        $this->assertFalse($str);
    }

    public function testBogusTemplates()
    {
        $str = Timber::compile(['assets/barf.twig', 'assets/lonestar.twig']);
        $this->assertFalse($str);
    }

    public function testTemplateChainWithMissingTwigFiles()
    {
        $str = Timber::compile(['assets/lonestar.twig', 'assets/single.twig']);
        $this->assertEquals('I am single.twig', trim($str));
    }

    public function testWhitespaceTrimForTemplate()
    {
        $str = Timber::compile('assets/single.twig ', []);
        $this->assertEquals('I am single.twig', trim($str));
    }

    /**
     * @expectedDeprecated  timber/loader/paths
     * @expectedDeprecated add_filter( 'timber/loader/paths', ['path/to/my/templates'] ) in a non-associative array
     */
    public function testTwigPathFilterAdded()
    {
        $php_unit = $this;
        add_filter('timber/loader/paths', function ($paths) use ($php_unit) {
            $paths[] = __DIR__ . '/october/';
            return $paths;
        });
        $str = Timber::compile('spooky.twig', []);
        $this->assertEquals('Boo!', $str);
    }

    /**
     * @expectedDeprecated  timber/loader/paths
     */
    public function testUpdatedTwigPathFilterAdded()
    {
        $php_unit = $this;
        add_filter('timber/loader/paths', function ($paths) use ($php_unit) {
            $paths[] = [__DIR__ . '/october/'];
            return $paths;
        });
        $str = Timber::compile('spooky.twig', []);
        $this->assertEquals('Boo!', $str);
    }

    /**
     * @expectedDeprecated  timber/loader/paths
     * @expectedDeprecated add_filter( 'timber/loader/paths', ['path/to/my/templates'] ) in a non-associative array
     */
    public function testTwigPathFilter()
    {
        switch_theme('timber-test-theme-child');

        $php_unit = $this;

        add_filter('timber/loader/paths', function ($paths) use ($php_unit) {
            $paths = call_user_func_array('array_merge', array_values($paths));
            $php_unit->assertSame(6, count($paths));
            $pos = array_search('/', $paths);
            unset($paths[$pos]);
            $php_unit->assertSame(5, count($paths));
            return $paths;
        });

        Timber::compile('assets/single.twig', []);

        switch_theme('default');
    }

    public function testTimberLocationsFilterAdded()
    {
        $php_unit = $this;
        add_filter('timber/locations', function ($paths) use ($php_unit) {
            $paths[] = [__DIR__ . '/october/'];
            return $paths;
        });
        $str = Timber::compile('spooky.twig', []);
        $this->assertEquals('Boo!', $str);
    }

    public function testTwigLoadsFromChildTheme()
    {
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/timber-test-theme-child/style.css');
        switch_theme('timber-test-theme-child');
        $child_theme = get_stylesheet_directory_uri();
        $this->assertEquals(WP_CONTENT_URL . '/themes/timber-test-theme-child', $child_theme);
        $context = [];
        $str = Timber::compile('single.twig', $context);
        $this->assertEquals('I am single.twig', trim($str));
        switch_theme('default');
    }

    public function testTwigLoadsFromParentTheme()
    {
        switch_theme('timber-test-theme-child');
        $templates = ['single-parent.twig'];
        $str = Timber::compile($templates, []);
        $this->assertEquals('I am single.twig in parent theme', trim($str));
        switch_theme('default');
    }

    public function _setupRelativeViews()
    {
        if (!file_exists(__DIR__ . '/views')) {
            mkdir(__DIR__ . '/views', 0777, true);
        }
        copy(__DIR__ . '/assets/relative.twig', __DIR__ . '/views/single.twig');
    }

    public function _teardownRelativeViews()
    {
        if (file_exists(__DIR__ . '/views/single.twig')) {
            unlink(__DIR__ . '/views/single.twig');
        }
        if (file_exists(__DIR__ . '/views')) {
            rmdir(__DIR__ . '/views');
        }
    }

    public function testTwigLoadsFromRelativeToScript()
    {
        $this->_setupRelativeViews();
        $str = Timber::compile('single.twig');
        $this->assertEquals('I am in the assets directory', trim($str));
        $this->_teardownRelativeViews();
    }

    public function testTwigLoadsFromAbsolutePathOnServer()
    {
        $str = Timber::compile(__DIR__ . '/assets/image-test.twig');
        $this->assertEquals('<img src="" />', trim($str));
    }

    public function _testTwigLoadsFromAbsolutePathOnServerWithSecurityRestriction()
    {
        $str = Timber::compile('assets/single-foo.twig');
    }

    public function testTwigLoadsFromAlternateDirName()
    {
        switch_theme('timber-test-theme');

        Timber::$dirname = [
            Timber\Loader::MAIN_NAMESPACE => ['foo', 'views'],
        ];
        if (!file_exists(get_template_directory() . '/foo')) {
            mkdir(get_template_directory() . '/foo', 0777, true);
        }
        copy(__DIR__ . '/assets/single-foo.twig', get_template_directory() . '/foo/single-foo.twig');
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', trim($str));

        switch_theme('default');
    }

    public function testTwigLoadsFromAlternateDirNameWithoutNamespace()
    {
        switch_theme('timber-test-theme');

        Timber::$dirname = [['foo', 'views']];
        if (!file_exists(get_template_directory() . '/foo')) {
            mkdir(get_template_directory() . '/foo', 0777, true);
        }
        copy(__DIR__ . '/assets/single-foo.twig', get_template_directory() . '/foo/single-foo.twig');
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', trim($str));

        switch_theme('default');
    }

    public function testTwigLoadsFromAlternateDirNameWithoutNamespaceAndSimpleArray()
    {
        switch_theme('timber-test-theme');

        Timber::$dirname = ['foo', 'views'];
        if (!file_exists(get_template_directory() . '/foo')) {
            mkdir(get_template_directory() . '/foo', 0777, true);
        }
        copy(__DIR__ . '/assets/single-foo.twig', get_template_directory() . '/foo/single-foo.twig');
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', trim($str));

        switch_theme('default');
    }

    public function testTwigLoadsFromLocation()
    {
        Timber::$locations = __DIR__ . '/assets';
        $str = Timber::compile('thumb-test.twig');
        $this->assertEquals('<img src="" />', trim($str));
    }

    public function testTwigLoadsFromLocationWithNamespace()
    {
        Timber::$locations = [
            __DIR__ . '/assets' => 'assets',
        ];
        $str = Timber::compile('@assets/thumb-test.twig');
        $this->assertEquals('<img src="" />', trim($str));
    }

    public function testTwigLoadsFromLocationWithNestedNamespace()
    {
        Timber::$locations = [
            __DIR__ . '/namespaced' => 'namespaced',
        ];
        $str = Timber::compile('@namespaced/test-nested.twig');
        $this->assertEquals('This is a namespaced template.', trim($str));
    }

    public function testTwigLoadsFromLocationWithAndWithoutNamespaces()
    {
        Timber::$locations = [
            __DIR__ . '/namespaced' => 'namespaced',
            __DIR__ . '/assets',
        ];

        // Namespaced location
        $str = Timber::compile('@namespaced/test-namespaced.twig');
        $this->assertEquals('This is a namespaced template.', trim($str));

        // Non namespaced location
        $str = Timber::compile('thumb-test.twig');
        $this->assertEquals('<img src="" />', trim($str));
    }

    public function testTwigLoadsFromLocationWithAndWithoutNamespacesAndDirs()
    {
        switch_theme('timber-test-theme');

        Timber::$dirname = [
            Timber\Loader::MAIN_NAMESPACE => ['foo', 'views'],
        ];
        Timber::$locations = [
            __DIR__ . '/namespaced' => 'namespaced',
            __DIR__ . '/assets',
        ];

        // Namespaced location
        $str = Timber::compile('@namespaced/test-namespaced.twig');
        $this->assertEquals('This is a namespaced template.', trim($str));

        // Non namespaced location
        $str = Timber::compile('thumb-test.twig');
        $this->assertEquals('<img src="" />', trim($str));

        if (!file_exists(get_template_directory() . '/foo')) {
            mkdir(get_template_directory() . '/foo', 0777, true);
        }
        copy(__DIR__ . '/assets/single-foo.twig', get_template_directory() . '/foo/single-foo.twig');

        // Dir
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', trim($str));

        switch_theme('default');
    }

    public function testTwigLoadsFromMultipleLocationsWithNamespace()
    {
        Timber::$locations = [
            __DIR__ . '/assets' => 'assets',
            __DIR__ . '/namespaced' => 'assets',
        ];
        $str = Timber::compile('@assets/thumb-test.twig');
        $this->assertEquals('<img src="" />', trim($str));

        $str = Timber::compile('@assets/test-namespaced.twig');
        $this->assertEquals('This is a namespaced template.', trim($str));
    }

    public function testTwigLoadsFirstTemplateWhenMultipleLocationsWithSameNamespace()
    {
        Timber::$locations = [
            __DIR__ . '/assets' => 'assets',
            __DIR__ . '/namespaced' => 'assets',
        ];
        $str = Timber::compile('@assets/thumb-test.twig');
        $this->assertEquals('<img src="" />', trim($str));
    }

    public function testTwigLoadsFromNotStandardDirectoryInChildTheme()
    {
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/timber-test-theme-child-non-standard/style.css');
        switch_theme('timber-test-theme-child-non-standard');
        $parent_theme_dir = get_template_directory();

        // Load parent theme functions.php specifically from this directory to fake the caller location.
        require_once $parent_theme_dir . '/functions.php';

        $child_theme = get_stylesheet_directory_uri();
        $this->assertEquals(WP_CONTENT_URL . '/themes/timber-test-theme-child-non-standard', $child_theme);
        $context = [];
        $str = Timber::compile('single.twig', $context);
        $this->assertEquals('I am single.twig', trim($str));
        switch_theme('default');

        // Reset the Timber::$dirname to the default value.
        Timber::$dirname = 'views';
    }
}
