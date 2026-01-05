<?php

namespace Timber\Tests\Support\Attributes;

use Attribute;

/**
 * Set a WordPress option for the duration of a test.
 *
 * The option will be set before the test runs and automatically
 * restored to its original value after the test completes.
 *
 * Can be applied to both test classes and individual test methods.
 * Method-level attributes take precedence over class-level ones.
 *
 * @example
 * ```php
 * #[WithOption('show_on_front', 'page')]
 * public function testStaticFrontPage(): void
 * {
 *     // show_on_front is 'page' during this test
 * }
 * ```
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class WithOption
{
    /**
     * Constructor.
     *
     * @param string $option The option name.
     * @param mixed  $value  The value to set for the duration of the test.
     */
    public function __construct(
        public readonly string $option,
        public readonly mixed $value
    ) {
    }
}
