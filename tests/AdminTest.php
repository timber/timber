<?php

namespace Timber\Tests;

use Timber\Admin;
use Timber\Timber;

class AdminTest extends TimberIntegrationTestCase
{
    public function tear_down()
    {
        \remove_all_actions('admin_notices');
        parent::tear_down();
    }

    public function testNoticeShownWhenWpVersionBelowMinimum(): void
    {
        Admin::init('6.0.0');

        \ob_start();
        \do_action('admin_notices');
        $output = \ob_get_clean();

        $this->assertStringContainsString(Timber::MINIMUM_WP_VERSION, $output);
        $this->assertStringContainsString('6.0.0', $output);
        $this->assertStringContainsString('update WordPress', $output);
    }

    public function testNoNoticeWhenWpVersionMeetsMinimum(): void
    {
        Admin::init(Timber::MINIMUM_WP_VERSION);

        \ob_start();
        \do_action('admin_notices');
        $output = \ob_get_clean();

        $this->assertStringNotContainsString('timber/timber', $output);
    }

    public function testNoNoticeWhenWpVersionAboveMinimum(): void
    {
        Admin::init('99.0.0');

        \ob_start();
        \do_action('admin_notices');
        $output = \ob_get_clean();

        $this->assertStringNotContainsString('timber/timber', $output);
    }

    /**
     * WordPress reports a major release as `6.8`, not `6.8.0`. The two-segment string
     * must be treated as meeting the minimum, otherwise the notice shows on the exact
     * release Timber targets.
     */
    public function testNoNoticeWhenRunningMajorReleaseOfMinimum(): void
    {
        Admin::init('6.8');

        \ob_start();
        \do_action('admin_notices');
        $output = \ob_get_clean();

        $this->assertStringNotContainsString('timber/timber', $output);
    }

    /**
     * WordPress fires `admin_init` with no args, which reaches the callback as an
     * empty string rather than null. The version must still be resolved instead of
     * being compared as an empty string (which would make the notice show always).
     */
    public function testNoNoticeWhenVersionResolvedFromEmptyString(): void
    {
        Admin::init('');

        \ob_start();
        \do_action('admin_notices');
        $output = \ob_get_clean();

        $this->assertStringNotContainsString('timber/timber', $output);
    }
}
