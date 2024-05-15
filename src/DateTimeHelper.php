<?php

namespace Timber;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Class DateTimeHelper
 *
 * Helper class to work with dates and times.
 *
 * @api
 * @since 2.0.0
 */
class DateTimeHelper
{
    /**
     * Wrapper for wp_date().
     *
     * @api
     * @since 2.0.0
     *
     * @param null|string|false             $format   Optional. PHP date format. Will use the
     *                                                `date_format` option as a default.
     * @param string|int|DateTimeInterface $date     A date.
     * @param null|DateTimeZone            $timezone Optional. Timezone to output result in.
     *                                                Defaults to timezone from site settings.
     *
     * @return false|string
     */
    public static function wp_date($format = null, $date = null, $timezone = null)
    {
        if (null === $format) {
            $format = \get_option('date_format');
        }

        if (null === $date) {
            $timestamp = \time();
        } elseif ($date instanceof DateTimeInterface) {
            $timestamp = $date->getTimestamp();
        } elseif (\is_numeric($date) && (\strtotime($date) === false || \strlen($date) !== 8)) {
            $timestamp = \intval($date);
        } else {
            $timestamp = \strtotime($date);
        }

        if (\is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        return \wp_date($format, $timestamp, $timezone);
    }

    /**
     * Returns the difference between two times in a human readable format.
     *
     * Differentiates between past and future dates.
     *
     * @api
     * @see \human_time_diff()
     * @example
     * ```twig
     * {{ post.date('U')|time_ago }}
     * {{ post.date('Y-m-d H:i:s')|time_ago }}
     * {{ post.date(constant('DATE_ATOM'))|time_ago }}
     * ```
     *
     * @param int|string $from          Base date as a timestamp or a date string.
     * @param int|string $to            Optional. Date to calculate difference to as a timestamp or
     *                                  a date string. Default current time.
     * @param string     $format_past   Optional. String to use for past dates. To be used with
     *                                  `sprintf()`. Default `%s ago`.
     * @param string     $format_future Optional. String to use for future dates. To be used with
     *                                  `sprintf()`. Default `%s from now`.
     *
     * @return string
     */
    public static function time_ago($from, $to = null, $format_past = null, $format_future = null)
    {
        if (null === $format_past) {
            /* translators: %s: Human-readable time difference. */
            $format_past = \__('%s ago');
        }

        if (null === $format_future) {
            /* translators: %s: Human-readable time difference. */
            $format_future = \__('%s from now');
        }

        $to ??= \time();
        $to = \is_numeric($to)
            ? new DateTimeImmutable('@' . $to, \wp_timezone())
            : new DateTimeImmutable($to, \wp_timezone());
        $from = \is_numeric($from)
            ? new DateTimeImmutable('@' . $from, \wp_timezone())
            : new DateTimeImmutable($from, \wp_timezone());

        if ($from < $to) {
            return \sprintf($format_past, \human_time_diff($from->getTimestamp(), $to->getTimestamp()));
        } else {
            return \sprintf($format_future, \human_time_diff($to->getTimestamp(), $from->getTimestamp()));
        }
    }
}
