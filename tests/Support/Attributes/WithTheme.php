<?php

namespace Timber\Tests\Support\Attributes;

use Attribute;

/**
 * Switch to a theme for the duration of a test.
 *
 * The theme will be switched before the test runs and automatically
 * restored to the default theme after the test completes.
 *
 * @example
 * ```php
 * #[WithTheme('timber-test-theme')]
 * public function testThemeFeature(): void
 * {
 *     // Theme is 'timber-test-theme' during this test
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class WithTheme
{
    /**
     * Constructor.
     *
     * @param string $theme The theme slug to switch to.
     */
    public function __construct(
        public readonly string $theme
    ) {
    }
}
