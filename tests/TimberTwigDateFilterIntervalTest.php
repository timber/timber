<?php

namespace Timber\Tests;

use DateInterval;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Group;
use Timber\Timber;
use Twig\Environment;
use Twig\Extension\CoreExtension;

/**
 * Replicates Twig tests from twig/twig/tests/Fixtures/filters/date*.test
 */
#[Group('\Timber\Date')]
class TimberTwigDateFilterIntervalTest extends TimberIntegrationTestCase
{
    private string $original_date_format;

    private string $original_timezone_string;

    public function set_up()
    {
        parent::set_up();

        // Store original values
        $this->original_date_format = \get_option('date_format');
        $this->original_timezone_string = \get_option('timezone_string');

        \update_option('date_format', 'F j, Y H:i');
        \update_option('timezone_string', 'Europe/Paris');

        /**
         * We deliberately do not set a different default timezone with date_default_timezone_set()
         * like they do in the Twig tests, because in a WordPress context, you shouldn't do that.
         * Instead, we set the timezone_string in the WordPress options to Europe/Paris.
         */
    }

    public function tear_down()
    {
        // Restore original values
        \update_option('date_format', $this->original_date_format);
        \update_option('timezone_string', $this->original_timezone_string);

        parent::tear_down();
    }

    public function get_context()
    {
        return [
            'date1' => new DateInterval('P2D'),
            'date2' => new DateInterval('P2D'),
            // This should have no effect on \DateInterval formatting
            'timezone1' => new DateTimeZone('America/New_York'),
        ];
    }

    public function testDateFormat1()
    {
        $result = Timber::compile_string(
            "{{ date1|date }}",
            $this->get_context()
        );

        $this->assertEquals('2 days', $result);
    }

    public function testDateFormat2()
    {
        $result = Timber::compile_string(
            "{{ date1|date('%d days %h hours') }}",
            $this->get_context()
        );

        $this->assertEquals('2 days 0 hours', $result);
    }

    public function testDateFormat3()
    {
        $result = Timber::compile_string(
            "{{ date1|date('%d days %h hours', timezone1) }}",
            $this->get_context()
        );

        $this->assertEquals('2 days 0 hours', $result);
    }

    public function testDateFormat4()
    {
        \add_filter('timber/loader/twig', function (Environment $twig) {
            $twig->getExtension(CoreExtension::class)
                ->setDateFormat('Y-m-d', '%d days %h hours');

            return $twig;
        });

        $result = Timber::compile_string(
            "{{ date2|date }}",
            $this->get_context()
        );

        $this->assertEquals('2 days 0 hours', $result);
    }

    public function testDateFormat5()
    {
        $result = Timber::compile_string(
            "{{ date2|date('%d days') }}",
            $this->get_context()
        );

        $this->assertEquals('2 days', $result);
    }
}
