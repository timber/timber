<?php

class TestTimberClass extends Timber_UnitTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testConstantsDefining()
    {
        // Just testing to make sure the double call doesn’t error-out.
        Timber\Timber::init();
        Timber\Timber::init();
    }
}
