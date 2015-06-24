<?php

class TestTimberClass extends WP_UnitTestCase
{
    public function testConstantsDefining()
    {
        $timber = $GLOBALS['timber'];
        $timber->init_constants();
        $timber->init_constants();
        /* just testing to make sure the double call doesnt error-out */
    }
}
