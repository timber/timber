<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\IgnoreDeprecations;
use Timber\Loader;
use Timber\Timber;
use Twig\Loader\LoaderInterface;

class LoaderTest extends TimberIntegrationTestCase
{
    public function set_up()
    {
        parent::set_up();
        // Reset locations for this test class - we're testing location behavior specifically
        Timber::$locations = [];
    }

    public function testTwigLoaderFilter()
    {
        $php_unit = $this;
        \add_filter('timber/loader/loader', function ($loader) use ($php_unit) {
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
        Timber::$locations = $this->getFixturesDir();
        $str = Timber::compile(['assets/lonestar.twig', 'assets/single.twig']);
        $this->assertEquals('I am single.twig', \trim($str));
    }

    public function testWhitespaceTrimForTemplate()
    {
        Timber::$locations = $this->getFixturesDir();
        $str = Timber::compile('assets/single.twig ', []);
        $this->assertEquals('I am single.twig', \trim($str));
    }

    #[IgnoreDeprecations]
    public function testTwigPathFilterAdded()
    {
        $this->setExpectedDeprecated('timber/loader/paths');
        $this->setExpectedDeprecated("add_filter( 'timber/loader/paths', ['path/to/my/templates'] ) in a non-associative array");
        $php_unit = $this;
        \add_filter('timber/loader/paths', function ($paths) use ($php_unit) {
            $paths[] = __DIR__ . '/Fixtures/october/';
            return $paths;
        });
        $str = Timber::compile('spooky.twig', []);
        $this->assertEquals('Boo!', $str);
    }

    #[IgnoreDeprecations]
    public function testUpdatedTwigPathFilterAdded()
    {
        $this->setExpectedDeprecated('timber/loader/paths');
        $php_unit = $this;
        \add_filter('timber/loader/paths', function ($paths) use ($php_unit) {
            $paths[] = [__DIR__ . '/Fixtures/october/'];
            return $paths;
        });
        $str = Timber::compile('spooky.twig', []);
        $this->assertEquals('Boo!', $str);
    }

    #[IgnoreDeprecations]
    public function testTwigPathFilter()
    {
        $this->setExpectedDeprecated('timber/loader/paths');
        $this->setExpectedDeprecated("add_filter( 'timber/loader/paths', ['path/to/my/templates'] ) in a non-associative array");
        \switch_theme('timber-test-theme-child');

        $childThemeDir = \trailingslashit(\get_stylesheet_directory());
        $parentThemeDir = \trailingslashit(\get_template_directory());

        $receivedPaths = null;
        $filter = function ($paths) use (&$receivedPaths) {
            $receivedPaths = \call_user_func_array(array_merge(...), \array_values($paths));
            return $receivedPaths;
        };
        \add_filter('timber/loader/paths', $filter);

        Timber::compile('assets/single.twig', []);

        \remove_filter('timber/loader/paths', $filter);
        \switch_theme('default');

        // Verify essential paths are included
        $this->assertIsArray($receivedPaths);
        $this->assertContains($childThemeDir, $receivedPaths, 'Child theme directory should be in paths');
        $this->assertContains($childThemeDir . 'views/', $receivedPaths, 'Child theme views directory should be in paths');
        $this->assertContains($parentThemeDir, $receivedPaths, 'Parent theme directory should be in paths');
        $this->assertContains($parentThemeDir . 'views/', $receivedPaths, 'Parent theme views directory should be in paths');
        $this->assertContains('/', $receivedPaths, 'Root fallback should be in paths');
    }

    public function testTimberLocationsFilterAdded()
    {
        $php_unit = $this;
        \add_filter('timber/locations', function ($paths) use ($php_unit) {
            $paths[] = [__DIR__ . '/Fixtures/october/'];
            return $paths;
        });
        $str = Timber::compile('spooky.twig', []);
        $this->assertEquals('Boo!', $str);
    }

    public function testTwigLoadsFromChildTheme()
    {
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/timber-test-theme-child/style.css');
        \switch_theme('timber-test-theme-child');
        $child_theme = \get_stylesheet_directory_uri();
        $this->assertEquals(WP_CONTENT_URL . '/themes/timber-test-theme-child', $child_theme);
        $context = [];
        $str = Timber::compile('single.twig', $context);
        $this->assertEquals('I am single.twig', \trim($str));
        \switch_theme('default');
    }

    public function testTwigLoadsFromParentTheme()
    {
        \switch_theme('timber-test-theme-child');
        $templates = ['single-parent.twig'];
        $str = Timber::compile($templates, []);
        $this->assertEquals('I am single.twig in parent theme', \trim($str));
        \switch_theme('default');
    }

    public function _setupRelativeViews()
    {
        $views_dir = $this->getFixturesDir() . '/views';
        if (!\file_exists($views_dir)) {
            \mkdir($views_dir, 0777, true);
        }
        \copy($this->getFixtureAsset('relative.twig'), $views_dir . '/single.twig');
    }

    public function _teardownRelativeViews()
    {
        $views_dir = $this->getFixturesDir() . '/views';
        if (\file_exists($views_dir . '/single.twig')) {
            \unlink($views_dir . '/single.twig');
        }
        if (\file_exists($views_dir)) {
            \rmdir($views_dir);
        }
    }

    public function testTwigLoadsFromRelativeToScript()
    {
        Timber::$locations = $this->getFixturesDir() . '/views';
        $this->_setupRelativeViews();
        $str = Timber::compile('single.twig');
        $this->assertEquals('I am in the assets directory', \trim($str));
        $this->_teardownRelativeViews();
    }

    public function testTwigLoadsFromAbsolutePathOnServer()
    {
        $str = Timber::compile($this->getFixtureAsset('image-test.twig'));
        $this->assertEquals('<img src="" />', \trim($str));
    }

    public function _testTwigLoadsFromAbsolutePathOnServerWithSecurityRestriction()
    {
        $str = Timber::compile('assets/single-foo.twig');
    }

    public function testTwigLoadsFromAlternateDirName()
    {
        \switch_theme('timber-test-theme');

        Timber::$dirname = [
            Loader::MAIN_NAMESPACE => ['foo', 'views'],
        ];
        if (!\file_exists(\get_template_directory() . '/foo')) {
            \mkdir(\get_template_directory() . '/foo', 0777, true);
        }
        \copy($this->getFixtureAsset('single-foo.twig'), \get_template_directory() . '/foo/single-foo.twig');
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', \trim($str));

        \switch_theme('default');
    }

    public function testTwigLoadsFromAlternateDirNameWithoutNamespace()
    {
        \switch_theme('timber-test-theme');

        Timber::$dirname = [['foo', 'views']];
        if (!\file_exists(\get_template_directory() . '/foo')) {
            \mkdir(\get_template_directory() . '/foo', 0777, true);
        }
        \copy($this->getFixtureAsset('single-foo.twig'), \get_template_directory() . '/foo/single-foo.twig');
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', \trim($str));

        \switch_theme('default');
    }

    public function testTwigLoadsFromAlternateDirNameWithoutNamespaceAndSimpleArray()
    {
        \switch_theme('timber-test-theme');

        Timber::$dirname = ['foo', 'views'];
        if (!\file_exists(\get_template_directory() . '/foo')) {
            \mkdir(\get_template_directory() . '/foo', 0777, true);
        }
        \copy($this->getFixtureAsset('single-foo.twig'), \get_template_directory() . '/foo/single-foo.twig');
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', \trim($str));

        \switch_theme('default');
    }

    public function testTwigLoadsFromLocation()
    {
        Timber::$locations = $this->getFixturesDir() . '/assets';
        $str = Timber::compile('thumb-test.twig');
        $this->assertEquals('<img src="" />', \trim($str));
    }

    public function testTwigLoadsFromLocationWithNamespace()
    {
        Timber::$locations = [
            $this->getFixturesDir() . '/assets' => 'assets',
        ];
        $str = Timber::compile('@assets/thumb-test.twig');
        $this->assertEquals('<img src="" />', \trim($str));
    }

    public function testTwigLoadsFromLocationWithNestedNamespace()
    {
        Timber::$locations = [
            $this->getFixturesDir() . '/namespaced' => 'namespaced',
        ];
        $str = Timber::compile('@namespaced/test-nested.twig');
        $this->assertEquals('This is a namespaced template.', \trim($str));
    }

    public function testTwigLoadsFromLocationWithAndWithoutNamespaces()
    {
        Timber::$locations = [
            $this->getFixturesDir() . '/namespaced' => 'namespaced',
            $this->getFixturesDir() . '/assets',
        ];

        // Namespaced location
        $str = Timber::compile('@namespaced/test-namespaced.twig');
        $this->assertEquals('This is a namespaced template.', \trim($str));

        // Non namespaced location
        $str = Timber::compile('thumb-test.twig');
        $this->assertEquals('<img src="" />', \trim($str));
    }

    public function testTwigLoadsFromLocationWithAndWithoutNamespacesAndDirs()
    {
        \switch_theme('timber-test-theme');

        Timber::$dirname = [
            Loader::MAIN_NAMESPACE => ['foo', 'views'],
        ];
        Timber::$locations = [
            $this->getFixturesDir() . '/namespaced' => 'namespaced',
            $this->getFixturesDir() . '/assets',
        ];

        // Namespaced location
        $str = Timber::compile('@namespaced/test-namespaced.twig');
        $this->assertEquals('This is a namespaced template.', \trim($str));

        // Non namespaced location
        $str = Timber::compile('thumb-test.twig');
        $this->assertEquals('<img src="" />', \trim($str));

        if (!\file_exists(\get_template_directory() . '/foo')) {
            \mkdir(\get_template_directory() . '/foo', 0777, true);
        }
        \copy($this->getFixtureAsset('single-foo.twig'), \get_template_directory() . '/foo/single-foo.twig');

        // Dir
        $str = Timber::compile('single-foo.twig');
        $this->assertEquals('I am single-foo', \trim($str));

        \switch_theme('default');
    }

    public function testTwigLoadsFromMultipleLocationsWithNamespace()
    {
        Timber::$locations = [
            $this->getFixturesDir() . '/assets' => 'assets',
            $this->getFixturesDir() . '/namespaced' => 'assets',
        ];
        $str = Timber::compile('@assets/thumb-test.twig');
        $this->assertEquals('<img src="" />', \trim($str));

        $str = Timber::compile('@assets/test-namespaced.twig');
        $this->assertEquals('This is a namespaced template.', \trim($str));
    }

    public function testTwigLoadsFirstTemplateWhenMultipleLocationsWithSameNamespace()
    {
        Timber::$locations = [
            $this->getFixturesDir() . '/assets' => 'assets',
            $this->getFixturesDir() . '/namespaced' => 'assets',
        ];
        $str = Timber::compile('@assets/thumb-test.twig');
        $this->assertEquals('<img src="" />', \trim($str));
    }

    public function testTwigLoadsFromNotStandardDirectoryInChildTheme()
    {
        $this->assertFileExists(WP_CONTENT_DIR . '/themes/timber-test-theme-child-non-standard/style.css');
        \switch_theme('timber-test-theme-child-non-standard');
        $parent_theme_dir = \get_template_directory();

        // Load parent theme functions.php specifically from this directory to fake the caller location.
        require_once $parent_theme_dir . '/functions.php';

        $child_theme = \get_stylesheet_directory_uri();
        $this->assertEquals(WP_CONTENT_URL . '/themes/timber-test-theme-child-non-standard', $child_theme);
        $context = [];
        $str = Timber::compile('single.twig', $context);
        $this->assertEquals('I am single.twig', \trim($str));
        \switch_theme('default');

        // Reset the Timber::$dirname to the default value.
        Timber::$dirname = 'views';
    }
}
