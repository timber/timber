<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;

/**
 * Replicates Twig tests from twig/twig/tests/Fixtures/filters/number_format*.test
 */
#[Group('\Timber\Number')]
class TimberTwigNumberFormatFilterTest extends TimberIntegrationTestCase
{
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
        $result = Timber::compile_string(
            "{{ number1|number_format }}",
            $this->get_context()
        );

        $this->assertEquals('20', $result);
    }

    public function testNumberFormat2()
    {
        $result = Timber::compile_string(
            "{{ number2|number_format }}",
            $this->get_context()
        );

        $this->assertEquals('20', $result);
    }

    public function testNumberFormat3()
    {
        $result = Timber::compile_string(
            "{{ number2|number_format(2) }}",
            $this->get_context()
        );

        $this->assertEquals('20.25', $result);
    }

    public function testNumberFormat4()
    {
        $result = Timber::compile_string(
            "{{ number2|number_format(2, ',') }}",
            $this->get_context()
        );

        $this->assertEquals('20,25', $result);
    }

    public function testNumberFormat5()
    {
        $result = Timber::compile_string(
            "{{ number3|number_format(2, ',') }}",
            $this->get_context()
        );

        $this->assertEquals('1,020,25', $result);
    }

    public function testNumberFormat6()
    {
        $result = Timber::compile_string(
            "{{ number3|number_format(2, ',', '.') }}",
            $this->get_context()
        );

        $this->assertEquals('1.020,25', $result);
    }
}
