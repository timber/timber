<?php

use Timber\DateTimeHelper;
use Timber\Timber;

/**
 * Class TestTimberDates
 *
 * @group called-post-constructor
 * @group Timber\Date
 */
class TestTimberDates extends Timber_UnitTestCase
{
    public function testDate()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = 'I am from {{post.date}}';
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I am from ' . date('F j, Y'), $str);
    }

    public function testTimeAgoFuture()
    {
        $str = DateTimeHelper::time_ago('2016-12-01 02:00:00', '2016-11-30, 02:00:00');
        $this->assertEquals('1 day from now', $str);
    }

    public function testTimeAgoPast()
    {
        $str = DateTimeHelper::time_ago('2016-11-29 02:00:00', '2016-11-30, 02:00:00');
        $this->assertEquals('1 day ago', $str);
    }

    public function testTimeAgoWithPostDate()
    {
        $pid = $this->factory->post->create([
            'post_date' => '2016-07-07 02:03:00',
        ]);
        $post = Timber::get_post($pid);

        $str = DateTimeHelper::time_ago($post->date(), '2016-11-30, 02:00:00');
        $this->assertEquals('5 months ago', $str);
    }

    public function testTimeAgoWithPostDateAndCurrent()
    {
        $pid = $this->factory->post->create([
            'post_date' => '2016-07-07 02:03:00',
        ]);
        $post = Timber::get_post($pid);

        $str = DateTimeHelper::time_ago($post->date());
        $this->assertEquals(
            sprintf('%s ago', human_time_diff(strtotime($post->post_date))),
            $str
        );
    }

    public function testTimeAgoWithPostDateTwigFilter()
    {
        $pid = $this->factory->post->create([
            'post_date' => '2016-07-07 02:03:00',
        ]);
        $post = Timber::get_post($pid);

        $current_ago = DateTimeHelper::time_ago($post->date, time());
        $str = Timber::compile_string(
            '{{ post.date|time_ago }}',
            [
                'post' => $post,
            ]
        );

        $this->assertEquals($current_ago, $str);
    }

    /**
     * @ticket https://github.com/timber/timber/issues/2737
     * @return void
     */
    public function testTimeAgoWithPostDateTwigFilterTimezoneAustralia()
    {
        $timezone_backup = get_option('timezone_string');

        // Set timezone to Australia/Adelaide.
        update_option('timezone_string', 'Australia/Adelaide');

        $current_time = current_datetime();
        // Subtract 3 hours to get a time in the past.
        $post_date = $current_time->sub(new DateInterval('PT3H'));

        $post_id = $this->factory->post->create([
            'post_date' => $post_date->format('Y-m-d H:i:s'),
        ]);
        $post = Timber::get_post($post_id);

        $diff1 = Timber::compile_string(
            "{{ post.date('U')|time_ago }}",
            [
                'post' => $post,
            ]
        );

        $diff2 = Timber::compile_string(
            "{{ post.date(constant('DATE_ATOM'))|time_ago }}",
            [
                'post' => $post,
            ]
        );

        $diff3 = Timber::compile_string(
            "{{ post.date('Y-m-d H:i:s')|time_ago }}",
            [
                'post' => $post,
            ]
        );

        $this->assertEquals('3 hours ago', $diff1);
        $this->assertEquals('3 hours ago', $diff2);
        $this->assertEquals('3 hours ago', $diff3);

        update_option('timezone_string', $timezone_backup);
    }

    public function testTimeAgoLabels()
    {
        $past = DateTimeHelper::time_ago('2016-11-29 02:00:00', '2016-11-30, 02:00:00', 'prePast %s afterPast');
        $future = DateTimeHelper::time_ago('2016-12-01 02:00:00', '2016-11-30, 02:00:00', null, 'preFuture %s afterFuture');
        $this->assertEquals('prePast 1 day afterPast', $past);
        $this->assertEquals('preFuture 1 day afterFuture', $future);
    }

    public function testTime()
    {
        $pid = $this->factory->post->create([
            'post_date' => '2016-07-07 02:03:00',
        ]);
        $post = Timber::get_post($pid);
        $twig = 'Posted at {{post.time}}';
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('Posted at 2:03 am', $str);
    }

    public function testPostDisplayDate()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = 'I am from {{post.date}}';
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I am from ' . date(get_option('date_format')), $str);
    }

    public function testPostDisplayDateTimezoneDifference()
    {
        // Switch timezone.
        update_option('timezone_string', 'America/Los_Angeles');

        $date_format = DATE_ATOM;
        $timezone = new DateTimeZone('Australia/Sydney');

        $pid = $this->factory->post->create([
            'post_date' => '2016-07-07 02:03:00',
        ]);

        $post = Timber::get_post($pid);
        $twig = "{{ post.date(date_format) }}";
        $str = Timber::compile_string($twig, [
            'post' => $post,
            'date_format' => $date_format,
            'timezone' => $timezone,
        ]);

        $this->assertEquals('2016-07-07T02:03:00-07:00', $str);
    }

    public function testPostDate()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = 'I am from {{post.post_date}}';
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I am from ' . $post->post_date, $str);
    }

    public function testPostDateWithDateFilter()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = 'I am from {{post.post_date|date}}';
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I am from ' . date('F j, Y'), $str);
    }

    public function testPostDateFunctionWithDateFilter()
    {
        $post_id = $this->factory->post->create([
            'post_date' => '2016-07-07 02:03:00',
        ]);
        $post = Timber::get_post($post_id);

        $template = "{{ post.date|date('j. F Y') }}";
        $result = Timber::compile_string($template, [
            'post' => $post,
        ]);

        $this->assertEquals($post->date('j. F Y'), $result);
    }

    public function testModifiedDate()
    {
        $date = date('F j, Y @ g:i a');
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = "I was modified {{ post.modified_date('F j, Y @ g:i a') }}";
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I was modified ' . $date, $str);
    }

    public function testModifiedDateFilter()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        add_filter('get_the_modified_date', function ($the_date) {
            return 'foobar';
        });
        $twig = "I was modified {{post.modified_date('F j, Y @ g:i a')}}";
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I was modified foobar', $str);
    }

    public function testModifiedTime()
    {
        $date = date('F j, Y @ g:i a');
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = "I was modified {{post.modified_time('F j, Y @ g:i a')}}";
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I was modified ' . $date, $str);
    }

    public function testInternationalTime()
    {
        $date = new DateTime('2015-09-28 05:00:00', new DateTimeZone('Europe/Amsterdam'));
        $twig = "{{'" . $date->format('g:i') . "'|date('g:i')}}";
        $str = Timber::compile_string($twig);
        $this->assertEquals('5:00', $str);
    }

    public function testModifiedTimeFilter()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        add_filter('get_the_modified_time', function ($the_date) {
            return 'foobar';
        });
        $twig = "I was modified {{post.modified_time('F j, Y @ g:i a')}}";
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I was modified foobar', $str);
    }

    public function testACFDate()
    {
        $twig = "Thing is on {{'20150928'|date('M j, Y')}}";
        $str = Timber::compile_string($twig);
        $this->assertEquals('Thing is on Sep 28, 2015', $str);
    }

    public function testUnixDate()
    {
        $twig = "Thing is on {{'1446127859'|date('M j, Y')}}";
        $str = Timber::compile_string($twig);
        $this->assertEquals('Thing is on Oct 29, 2015', $str);
    }

    public function testUnixDateEdgeCase()
    {
        $twig = "Thing is on {{'1457395200'|date('M j, Y')}}";
        $str = Timber::compile_string($twig);
        $this->assertEquals('Thing is on Mar 8, 2016', $str);
    }

    public function testEightDigitsString()
    {
        $twig = "Thing is on {{'20160505'|date('M j, Y')}}";
        $str = Timber::compile_string($twig);
        $this->assertEquals('Thing is on May 5, 2016', $str);
    }

    public function testEightDigits()
    {
        $twig = "Thing is on {{20160505|date('M j, Y')}}";
        $str = Timber::compile_string($twig);
        $this->assertEquals('Thing is on May 5, 2016', $str);
    }

    public function testSeventiesDates()
    {
        $twig = "Nixon was re-elected on {{'89942400'|date('M j, Y')}}, long may he reign!";
        $str = Timber::compile_string($twig);
        $this->assertEquals('Nixon was re-elected on Nov 7, 1972, long may he reign!', $str);
    }

    public function testDateNowFunctionTwig()
    {
        $twig = "{{ date('now')|date('F j, Y @ g:i a') }}";
        $str = Timber::compile_string($twig);

        $this->assertEquals(wp_date('F j, Y @ g:i a'), $str);
    }

    public function testTimeAgoFutureTranslated()
    {
        $this->switch_to_locale('de_DE');
        $str = DateTimeHelper::time_ago('2016-12-01 20:00:00', '2016-11-30, 20:00:00');
        $this->assertEquals('1 Tag ab jetzt', $str);

        restore_current_locale();
    }

    public function testTimeAgoPastTranslated()
    {
        $this->switch_to_locale('de_DE');
        $str = DateTimeHelper::time_ago('2016-11-29 20:00:00', '2016-11-30, 20:00:00');
        $this->assertEquals('vor 1 Tag', $str);

        restore_current_locale();
    }

    public function testPostDateWithFilter()
    {
        $pid = $this->factory->post->create();
        $post = Timber::get_post($pid);
        $twig = 'I am from {{post.post_date|date}}';
        $str = Timber::compile_string($twig, [
            'post' => $post,
        ]);
        $this->assertEquals('I am from ' . date('F j, Y'), $str);
    }
}
