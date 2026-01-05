<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Timber;

/**
 * Replicates Twig tests from twig/twig/tests/Fixtures/filters/date_default*.test
 */
#[Group('\Timber\Date')]
#[WithOption('date_format', 'Y-m-d')]
class TimberTwigDateFilterDefaultTest extends TimberIntegrationTestCase
{
    public function get_context()
    {
        return [
            'date1' => \mktime(13, 45, 0, 10, 4, 2010),
        ];
    }

    public function testDateFormat1()
    {
        $result = Timber::compile_string(
            "{{ date1|date }}",
            $this->get_context()
        );

        $this->assertEquals('2010-10-04', $result);
    }

    public function testDateFormat2()
    {
        $result = Timber::compile_string(
            "{{ date1|date('d/m/Y') }}",
            $this->get_context()
        );

        $this->assertEquals('04/10/2010', $result);
    }

    public function testDateFormat3()
    {
        $result = Timber::compile_string(
            "{{ date1|date(format='d/m/Y H:i:s P', timezone='America/Chicago') }}",
            $this->get_context()
        );

        $this->assertEquals('04/10/2010 08:45:00 -05:00', $result);
    }

    public function testDateFormat4()
    {
        $result = Timber::compile_string(
            "{{ date1|date(timezone='America/Chicago', format='d/m/Y H:i:s P') }}",
            $this->get_context()
        );

        $this->assertEquals('04/10/2010 08:45:00 -05:00', $result);
    }

    public function testDateFormat5()
    {
        $result = Timber::compile_string(
            "{{ date1|date('d/m/Y H:i:s P', timezone='America/Chicago') }}",
            $this->get_context()
        );

        $this->assertEquals('04/10/2010 08:45:00 -05:00', $result);
    }
}
