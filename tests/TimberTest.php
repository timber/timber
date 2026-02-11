<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Timber\Timber;

class TimberTest extends TimberIntegrationTestCase
{
    #[DoesNotPerformAssertions]
    public function testConstantsDefining()
    {
        // Just testing to make sure the double call doesn’t error-out.
        Timber::init();
        Timber::init();
    }
}
