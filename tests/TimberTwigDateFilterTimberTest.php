<?php

namespace Timber\Tests;

use DateTime;
use PHPUnit\Framework\Attributes\Group;
use Timber\Tests\Support\Attributes\WithLocale;
use Timber\Tests\Support\Attributes\WithOption;
use Timber\Timber;

/**
 * Replicates Twig tests from twig/twig/tests/Fixtures/filters/date*.test
 */
#[Group('\Timber\Date')]
class TimberTwigDateFilterTimberTest extends TimberIntegrationTestCase
{
    public function testTwigFilterDate()
    {
        $date = '1983-09-28';
        $data['bday'] = $date;
        $str = Timber::compile_string("{{bday|date('M j, Y')}}", $data);
        $this->assertEquals('Sep 28, 1983', \trim($str));
        $data['bday'] = new DateTime($date);
        $str = Timber::compile_string("{{bday|date('M j, Y')}}", $data);
        $this->assertEquals('Sep 28, 1983', \trim($str));
    }

    public function testTwigFilterDateWordPressFormat()
    {
        $data['day'] = '2012-10-15 20:14:48';
        $str = Timber::compile_string("{{day|date('M jS, Y g:ia')}}", $data);
        $this->assertEquals('Oct 15th, 2012 8:14pm', \trim($str));
    }

    public function testTwigFilterNow()
    {
        $now = \date('M jS, Y');
        $str = Timber::compile_string("{{now|date('M jS, Y')}}");
        $this->assertSame($now, $str);
        $str = Timber::compile_string("{{null|date('M jS, Y')}}");
        $this->assertSame($now, $str);
        $str = Timber::compile_string("{{'now'|date('M jS, Y')}}");
        $this->assertSame($now, $str);
    }

    #[WithLocale('es_ES')]
    public function testTwigFilterDateI18n()
    {
        $data['day'] = '1983-09-28 20:14:48';
        $str = Timber::compile_string("{{day|date('F jS, Y g:ia')}}", $data);
        $this->assertEquals('septiembre 28th, 1983 8:14pm', $str);
    }

    #[WithLocale('es_ES')]
    #[WithOption('date_format', 'j F, Y')]
    public function testTwigFilterDateI18nWordPressOption()
    {
        $data['day'] = '1983-09-28';
        $str = Timber::compile_string("{{day|date}}", $data);
        $this->assertEquals('28 septiembre, 1983', $str);
    }

    public function testTwigFilterDateWordPressOption()
    {
        $format = \get_option('date_format');
        $str = Timber::compile_string("{{ now|date('{$format}') }}");
        $empty = Timber::compile_string("{{ now|date }}");

        $this->assertSame($str, $empty);
    }
}
