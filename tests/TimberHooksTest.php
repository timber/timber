<?php

namespace Timber\Tests;

use Timber\Timber;

class TimberHooksTest extends TimberIntegrationTestCase
{
    public function testTimberContext()
    {
        $this->add_filter_temporarily('timber/context', function ($context) {
            $context['person'] = "Nathan Hass";
            return $context;
        });
        $context = Timber::context();
        $this->assertEquals('Nathan Hass', $context['person']);
    }
}
