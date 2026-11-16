<?php

namespace Timber\Tests;

use Timber\Loader;
use Timber\Timber;
use Twig\Environment;
use Twig\Error\LoaderError;

require_once __DIR__ . '/Fixtures/shared-environment/caller-a/functions.php';
require_once __DIR__ . '/Fixtures/shared-environment/caller-b/functions.php';

/**
 * Covers the `timber/twig/environment/shared` filter.
 */
class LoaderSharedEnvironmentTest extends TimberIntegrationTestCase
{
    private int $builds = 0;

    public function set_up()
    {
        parent::set_up();

        $this->builds = 0;
        \add_filter('timber/twig', [$this, 'count_build']);
    }

    public function tear_down()
    {
        try {
            // Empties the shared slot while the opt-in filter is still registered, so the
            // environment it reads is the shared one and no throwaway build runs.
            (new Loader())->clear_cache_twig();
        } finally {
            \remove_filter('timber/twig/environment/shared', '__return_true');
            \remove_filter('timber/twig', [$this, 'count_build']);

            parent::tear_down();
        }
    }

    public function count_build(Environment $twig): Environment
    {
        $this->builds++;

        return $twig;
    }

    private function enable_shared_environment(): void
    {
        \add_filter('timber/twig/environment/shared', '__return_true');
    }

    public function testBuildsAnEnvironmentPerRenderByDefault()
    {
        Timber::compile('shared-environment/plain.twig');
        Timber::compile('shared-environment/plain.twig');
        Timber::compile('shared-environment/plain.twig');

        $this->assertSame(3, $this->builds);
    }

    public function testReusesTheEnvironmentWhenShared()
    {
        $this->enable_shared_environment();

        $this->assertSame('plain', Timber::compile('shared-environment/plain.twig'));
        $this->assertSame('plain', Timber::compile('shared-environment/plain.twig'));
        $this->assertSame('plain', Timber::compile('shared-environment/plain.twig'));

        $this->assertSame(1, $this->builds);
    }

    public function testClearCacheTwigDiscardsTheSharedEnvironment()
    {
        $this->enable_shared_environment();

        Timber::compile('shared-environment/plain.twig');
        // clear_cache_twig() reads the cache location through get_twig(), which returns the
        // shared instance (no build), then empties the slot.
        (new Loader())->clear_cache_twig();
        Timber::compile('shared-environment/plain.twig');

        $this->assertSame(2, $this->builds);
    }

    public function testCompileStringReusesTheSharedEnvironment()
    {
        $this->enable_shared_environment();

        Timber::compile('shared-environment/plain.twig');

        $this->assertSame('2', Timber::compile_string('{{ 1 + 1 }}'));
        $this->assertSame(1, $this->builds);
    }

    public function testKeepsCallerRelativeLookupWhenShared()
    {
        $this->enable_shared_environment();

        $this->assertSame('caller-a', \timber_test_shared_env_compile_a());
        $this->assertSame('caller-b', \timber_test_shared_env_compile_b());
        $this->assertSame(1, $this->builds);
    }

    public function testRestoresTheOuterLoaderAfterANestedCompile()
    {
        $this->enable_shared_environment();

        // outer.twig (caller-a) triggers a compile from caller-b through function(), then
        // includes partial-a.twig, which only resolves with caller-a's loader.
        $this->assertSame('outer:caller-b:partial-a', \timber_test_shared_env_compile_outer());
        $this->assertSame(1, $this->builds);
    }

    public function testHonorsLoaderMutationsFromHooksByDefault()
    {
        $add_namespace = function (Environment $twig): Environment {
            $twig->getLoader()->addPath(__DIR__ . '/Fixtures/shared-environment/caller-b', 'shared_b');

            return $twig;
        };
        $this->add_filter_temporarily('timber/loader/twig', $add_namespace);

        $output = Timber::compile('shared-environment/includes-namespaced.twig');

        $this->assertSame('caller-b', $output);
    }

    public function testIgnoresHooksRegisteredAfterTheFirstBuildWhenShared()
    {
        $this->enable_shared_environment();
        Timber::compile('shared-environment/plain.twig');

        $late_runs = 0;
        $late = function (Environment $twig) use (&$late_runs): Environment {
            $late_runs++;

            return $twig;
        };
        $this->add_filter_temporarily('timber/twig', $late);

        Timber::compile('shared-environment/plain.twig');
        $this->assertSame(0, $late_runs);

        (new Loader())->clear_cache_twig();
        Timber::compile('shared-environment/plain.twig');
        $this->assertSame(1, $late_runs);
        $this->assertSame(2, $this->builds);
    }

    public function testCompileTwigBlockReusesTheSharedEnvironment()
    {
        $this->enable_shared_environment();

        Timber::compile('shared-environment/plain.twig');
        // Explicit caller and bare template name: toasts.twig only resolves through the block
        // loader's own paths, so this also proves the loader swap through the subclass.
        $output = Timber::compile_twig_block('error', 'toasts.twig', [
            'message' => 'shared',
        ], __DIR__ . '/Fixtures/assets');

        $this->assertStringContainsString('role="alert">shared</div>', $output);
        $this->assertSame(1, $this->builds);
    }

    public function testResolvesTheExtendsParentPerCallerByDefault()
    {
        $this->assertSame('a:child', \timber_test_shared_env_compile_child_a());
        $this->assertSame('b:child', \timber_test_shared_env_compile_child_b());
    }

    public function testResolvesTheExtendsParentPerCallerWhenShared()
    {
        $this->enable_shared_environment();

        // Twig reloads a constant {% extends %} parent on every render, so it follows the
        // loader swapped in for the current caller.
        $this->assertSame('a:child', \timber_test_shared_env_compile_child_a());
        $this->assertSame('b:child', \timber_test_shared_env_compile_child_b());
        $this->assertSame(1, $this->builds);
    }

    public function testResolvesUseTraitsPerCallerByDefault()
    {
        $this->assertSame('a', \timber_test_shared_env_compile_uses_a());
        $this->assertSame('b', \timber_test_shared_env_compile_uses_b());
    }

    public function testKeepsTheFirstUseTraitsWhenShared()
    {
        $this->enable_shared_environment();

        // Twig loads {% use %} traits in the compiled template's constructor and keeps one
        // Template instance per file in the environment, so the second caller gets caller-a's
        // traits.
        $this->assertSame('a', \timber_test_shared_env_compile_uses_a());
        $this->assertSame('a', \timber_test_shared_env_compile_uses_b());
        $this->assertSame(1, $this->builds);
    }

    public function testResolvesTheExtendsParentPerCallerForBlockRendersByDefault()
    {
        $a = __DIR__ . '/Fixtures/shared-environment/caller-a';
        $b = __DIR__ . '/Fixtures/shared-environment/caller-b';

        $this->assertSame('a', Timber::compile_twig_block('title', 'shared-environment/child-block.twig', [], $a));
        $this->assertSame('b', Timber::compile_twig_block('title', 'shared-environment/child-block.twig', [], $b));
    }

    public function testKeepsTheFirstExtendsParentForBlockRendersWhenShared()
    {
        $this->enable_shared_environment();

        $a = __DIR__ . '/Fixtures/shared-environment/caller-a';
        $b = __DIR__ . '/Fixtures/shared-environment/caller-b';

        // Block renders never run doDisplay(), where Twig reloads a constant parent, so the
        // parent resolved by the first block render sticks to the shared Template instance.
        $this->assertSame('a', Timber::compile_twig_block('title', 'shared-environment/child-block.twig', [], $a));
        $this->assertSame('a', Timber::compile_twig_block('title', 'shared-environment/child-block.twig', [], $b));
        $this->assertSame(1, $this->builds);
    }

    public function testIgnoresLoaderMutationsFromHooksWhenShared()
    {
        $this->enable_shared_environment();
        $this->add_filter_temporarily('timber/loader/twig', function (Environment $twig): Environment {
            $twig->getLoader()->addPath(__DIR__ . '/Fixtures/shared-environment/caller-b', 'shared_b');

            return $twig;
        });

        try {
            Timber::compile('shared-environment/includes-namespaced.twig');
            $this->fail('Expected a LoaderError for the shared_b namespace.');
        } catch (LoaderError $e) {
            // The hook mutated the loader the environment was built with; the render swapped in
            // its own loader, which does not know the namespace.
            $this->assertStringContainsString('shared_b', $e->getMessage());
        }

        // The failed render restored the build-time loader, which does know the namespace.
        $this->assertNotEmpty((new Loader())->get_twig()->getLoader()->getPaths('shared_b'));
    }
}
