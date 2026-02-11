<?php

namespace Timber\Tests\Support\Attributes;

use Attribute;

/**
 * Set a locale for the duration of a test.
 *
 * The locale will be switched before the test runs and automatically
 * restored after the test completes.
 *
 * @example
 * ```php
 * #[WithLocale('de_DE')]
 * public function testGermanTranslation(): void
 * {
 *     // Locale is 'de_DE' during this test
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class WithLocale
{
    /**
     * Constructor.
     *
     * @param string $locale The locale code (e.g., 'de_DE', 'fr_FR').
     */
    public function __construct(
        public readonly string $locale
    ) {
    }
}
