<?php

class TestTimberAdmin extends WP_UnitTestCase
{
    public function testSettingsLinks()
    {
        $links = apply_filters('plugin_row_meta', array(), 'timber/timber.php');

        $links = implode(' ', $links);
        $this->assertContains('Documentation', $links);

        $links = apply_filters('plugin_row_meta', array(), 'foo/bar.php');
        if (isset($links)) {
            $this->assertNotContains('Documentation', $links);
        }
    }

    public function testAdminInit()
    {
        $admin = TimberAdmin::init();
        $this->assertTrue($admin);
    }
}
