<?php

namespace Timber;

/**
 * Class DateTimeHelper
 *
 * Helper class to work with dates and times.
 *
 * @api
 * @since 2.0.0
 */
class DateTimeHelper {
	/**
	 * Wrapper for wp_date().
	 *
	 * @api
	 * @since 2.0.0
	 *
	 * @param null|string|false             $format   Optional. PHP date format. Will use the
	 *                                                `date_format` option as a default.
	 * @param string|int|\DateTimeInterface $date     A date.
	 * @param null|\DateTimeZone            $timezone Optional. Timezone to output result in.
	 *                                                Defaults to timezone from site settings.
	 *
	 * @return false|string
	 */
	public static function wp_date( $format = null, $date = null, $timezone = null ) {
		if ( null === $format ) {
			$format = get_option( 'date_format' );
		}

		if ( null === $date ) {
			$timestamp = time();
		} elseif ( $date instanceof \DateTimeInterface ) {
			$timestamp = $date->getTimestamp();
		} elseif ( is_numeric( $date ) && ( strtotime( $date ) === false || strlen( $date ) !== 8 ) ) {
			$timestamp = intval( $date );
		} else {
			$timestamp = strtotime( $date );
		}

		if ( is_string( $timezone ) ) {
			$timezone = new \DateTimeZone( $timezone );
		}

		if ( function_exists( 'wp_date' ) ) {
			return wp_date( $format, $timestamp, $timezone );
		}

		/**
		 * Fallback for older date_i18n() function, which requires a timestamp including an offset.
		 *
		 * @TODO maybe delete this?
		 */
		if ( $date instanceof \DateTimeInterface ) {
			$timestamp = $date->getTimestamp() + $date->getOffset();
		}

		return date_i18n( $format, $timestamp );
	}

	/**
	 * Returns the difference between two times in a human readable format.
	 *
	 * Differentiates between past and future dates.
	 *
	 * @api
	 * @since 2.0.0
	 * @see \human_time_diff()
	 * @example
	 * ```
	 *
	 * ```
	 *
	 * @param int|string $from          Base date as a timestamp or a date string.
	 * @param int|string $to            Optional. Date to calculate difference to as a timestamp or
	 *                                  a date string. Default to current time.
	 * @param string     $format_past   Optional. String to use for past dates. To be used with
	 *                                  `sprintf()`. Default `%s ago`.
	 * @param string     $format_future Optional. String to use for future dates. To be used with
	 *                                  `sprintf()`. Default `%s from now`.
	 *
	 * @return string
	 */
	public static function time_ago( $from, $to = null, $format_past = '%s ago', $format_future = '%s from now' ) {
		$to   = null === $to ? time() : $to;
		$to   = is_int( $to ) ? $to : strtotime( $to );
		$from = is_int( $from ) ? $from : strtotime( $from );

		if ( $from < $to ) {
			return sprintf( $format_past, human_time_diff( $from, $to ) );
		} else {
			return sprintf( $format_future, human_time_diff( $to, $from ) );
		}
	}
}