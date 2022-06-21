<?php

class TestTimberGettext extends Timber_UnitTestCase
{
    public function test__()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ __('my_boo') }}", $context);
        $this->assertEquals(__('my_boo'), trim($str));
    }

    public function testTranslate()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ translate('my_boo') }}", $context);
        $this->assertEquals(translate('my_boo'), trim($str));
    }

    public function test_e()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ _e('my_boo') }}", $context);
        ob_start();
        _e('my_boo');
        $_e = ob_get_clean();
        $this->assertEquals($_e, trim($str));
    }

    public function test_n()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ _n('foo', 'foos', 1 ) }}", $context);
        $this->assertEquals(_n('foo', 'foos', 1), trim($str));
        $str = Timber::compile_string("{{ _n('foo', 'foos', 2 ) }}", $context);
        $this->assertEquals(_n('foo', 'foos', 2), trim($str));
    }

    public function test_x()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ _x('boo', 'my') }}", $context);
        $this->assertEquals(_x('boo', 'my'), trim($str));
    }

    public function test_ex()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ _ex('boo', 'my') }}", $context);
        ob_start();
        _ex('boo', 'my');
        $_ex = ob_get_clean();
        $this->assertEquals($_ex, trim($str));
    }

    public function test_nx()
    {
        $context = Timber::context();
        $str = Timber::compile_string("{{ _nx('boo', 'boos', 1, 'my' ) }}", $context);
        $this->assertEquals(_nx('boo', 'boos', 1, 'my'), trim($str));
        $str = Timber::compile_string("{{ _nx('boo', 'boos', 2, 'my' ) }}", $context);
        $this->assertEquals(_nx('boo', 'boos', 2, 'my'), trim($str));
    }
}
