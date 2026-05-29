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
}
