<?php

/**
 * Replicates Twig tests from twig/twig/tests/Fixtures/filters/number_format*.test
 *
 * @group Timber\Number
 */
class TestTimberTwigNumberFormatFilterDefault extends Timber_UnitTestCase
{
    public function set_up()
    {
        // Simulate fr_FR locale
        global $wp_locale;
        $wp_locale->number_format['decimal_point'] = ',';
        $wp_locale->number_format['thousands_sep'] = ' ';
        parent::set_up();
    }

    public function tear_down()
    {
        // Reset locale
        $GLOBALS['wp_locale'] = new WP_Locale();
        parent::tear_down();
    }

    public function get_context()
    {
        return [
            'number1' => 20,
            'number2' => 20.25,
            'number3' => 1020.25,
        ];
    }

    public function testNumberFormat1()
    {
        $result = Timber\Timber::compile_string(
            "{{ number1|number_format }}",
            $this->get_context()
        );

        $this->assertEquals('20', $result);
    }

    public function testNumberFormat2()
    {
        $result = Timber\Timber::compile_string(
            "{{ number2|number_format }}",
            $this->get_context()
        );

        $this->assertEquals('20', $result);
    }

    public function testNumberFormat3()
    {
        $result = Timber\Timber::compile_string(
            "{{ number2|number_format(2) }}",
            $this->get_context()
        );

        $this->assertEquals('20,25', $result);
    }

    public function testNumberFormat5()
    {
        $result = Timber\Timber::compile_string(
            "{{ number3|number_format }}",
            $this->get_context()
        );

        $this->assertEquals('1 020', $result);
    }

    public function testNumberFormat6()
    {
        $result = Timber\Timber::compile_string(
            "{{ number3|number_format(2) }}",
            $this->get_context()
        );

        $this->assertEquals('1 020,25', $result);
    }
}
