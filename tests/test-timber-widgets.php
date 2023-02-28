<?php

class TestTimberWidgets extends Timber_UnitTestCase
{
    /**
     * @requires PHP < 8.1
     */
    public function testHTML()
    {
        // Replace this with some actual testing code
        $content = Timber::get_widgets('sidebar-1');
        $content = trim($content);
        $this->assertEquals('<', substr($content, 0, 1));
    }

    /**
     * @requires PHP < 8.1
     */
    public function testManySidebars()
    {
        $sidebar1 = Timber::get_widgets('sidebar-1');
        $sidebar2 = Timber::get_widgets('sidebar-2');
        $this->assertGreaterThan(0, strlen($sidebar1));
    }
}
