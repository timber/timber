<?php

namespace Timber\Tests\Support\Concerns;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use ReflectionAttribute;
use Timber\Tests\Support\Attributes\WithLocale;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Tests\Support\Attributes\WithTheme;
use WP_Locale;

/**
 * Trait for managing WordPress state in tests.
 *
 * Provides automatic backup and restore functionality for:
 * - Options via #[WithOption] attribute or setOptionTemporarily()
 * - Locale via #[WithLocale] attribute
 * - Theme via #[WithTheme] attribute
 *
 * Uses Mantle's register_attribute() pattern for attribute handling.
 */
trait InteractsWithState
{
    /**
     * Original option values to restore after test.
     *
     * @var array<string, mixed>
     */
    private array $originalOptionValues = [];

    /**
     * Whether locale was switched and needs restoring.
     */
    private bool $localeWasSwitched = false;

    /**
     * Original theme to restore after test.
     */
    private ?string $originalTheme = null;

    /**
     * Register the WithOption attribute handler.
     */
    #[Before]
    public function registerWithOptionAttribute(): void
    {
        $this->register_attribute(
            WithOption::class,
            fn (ReflectionAttribute $attribute) => $this->setOptionTemporarily(
                $attribute->newInstance()->option,
                $attribute->newInstance()->value
            ),
        );
    }

    /**
     * Register the WithLocale attribute handler.
     */
    #[Before]
    public function registerWithLocaleAttribute(): void
    {
        $this->register_attribute(
            WithLocale::class,
            fn (ReflectionAttribute $attribute) => $this->switchToLocaleTemporarily(
                $attribute->newInstance()->locale
            ),
        );
    }

    /**
     * Register the WithTheme attribute handler.
     */
    #[Before]
    public function registerWithThemeAttribute(): void
    {
        $this->register_attribute(
            WithTheme::class,
            fn (ReflectionAttribute $attribute) => $this->switchThemeTemporarily(
                $attribute->newInstance()->theme
            ),
        );
    }

    /**
     * Restore original option values after each test.
     */
    #[After]
    public function restoreOriginalOptions(): void
    {
        foreach ($this->originalOptionValues as $option => $value) {
            if ($value === null) {
                \delete_option($option);
            } else {
                \update_option($option, $value);
            }
        }

        $this->originalOptionValues = [];
    }

    /**
     * Restore locale after each test if it was switched.
     */
    #[After]
    public function restoreLocale(): void
    {
        if ($this->localeWasSwitched) {
            // Unload the test textdomain that was loaded by switch_to_locale()
            // restore_current_locale() doesn't unload manually loaded textdomains
            \unload_textdomain('default');
            \restore_current_locale();

            // Reinitialize WP_Locale to restore English month names etc.
            $GLOBALS['wp_locale'] = new WP_Locale();

            $this->localeWasSwitched = false;
        }
    }

    /**
     * Restore theme after each test if it was switched.
     */
    #[After]
    public function restoreTheme(): void
    {
        if ($this->originalTheme !== null) {
            \switch_theme($this->originalTheme);
            $this->originalTheme = null;
        }
    }

    /**
     * Set an option temporarily for the current test.
     *
     * The original value will be restored after the test completes.
     * Can be called directly in tests for dynamic option values.
     *
     * @param string $option The option name.
     * @param mixed  $value  The value to set.
     */
    protected function setOptionTemporarily(string $option, mixed $value): void
    {
        // Only backup once per option per test
        if (!\array_key_exists($option, $this->originalOptionValues)) {
            $original = \get_option($option);
            $this->originalOptionValues[$option] = $original === false ? null : $original;
        }

        \update_option($option, $value);
    }

    /**
     * Switch to a locale temporarily for the current test.
     *
     * The locale will be restored after the test completes.
     *
     * @param string $locale The locale code (e.g., 'de_DE').
     */
    protected function switchToLocaleTemporarily(string $locale): void
    {
        \switch_to_locale($locale);

        // Load the file after switching to override wp tests languages files
        $tests_language_mo = __DIR__ . '/../../Fixtures/languages/' . $locale . '.mo';
        if (!\is_file($tests_language_mo)) {
            \wp_die('No language file found for ' . $locale);
        }
        \load_textdomain('default', $tests_language_mo);

        // Reinitialize WP_Locale so month names use our translations
        $GLOBALS['wp_locale'] = new WP_Locale();

        $this->localeWasSwitched = true;
    }

    /**
     * Switch to a theme temporarily for the current test.
     *
     * The theme will be restored after the test completes.
     *
     * @param string $theme The theme slug.
     */
    protected function switchThemeTemporarily(string $theme): void
    {
        if ($this->originalTheme === null) {
            $this->originalTheme = \get_stylesheet();
        }
        \switch_theme($theme);
    }
}
