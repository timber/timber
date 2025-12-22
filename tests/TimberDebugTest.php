<?php

namespace Timber\Tests;

use Timber\Timber;

class TimberDebugTest extends TimberIntegrationTestCase
{
    public function testCallingPHPFile()
    {
        $phpunit = $this;
        \add_filter('timber/calling_php_file', function ($file) use ($phpunit) {
            $phpunit->assertStringEndsWith('/tests/TimberDebugTest.php', $file);
        });
        Timber::compile('assets/output.twig');
    }
}
