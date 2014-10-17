<?php

class TimberTwigHelper {
    /**
     *
     *
     * @param mixed   $arr
     * @param string  $glue
     * @return string
     */
    function to_string( $arr, $glue = ' ' ) {
        if ( is_string( $arr ) ) {
            return $arr;
        }
        if ( is_array( $arr ) && count( $arr ) == 1 ) {
            return $arr[0];
        }
        if ( is_array( $arr ) ) {
            return implode( $glue, $arr );
        }
        return null;
    }

    /**
     *
     *
     * @param string  $function_name
     * @return mixed
     */
    function exec_function( $function_name ) {
        $args = func_get_args();
        array_shift( $args );
        return call_user_func_array( trim( $function_name ), ( $args ) );
    }
    /**
     *
     *
     * @param string  $date
     * @param string  $format (optional)
     * @return string
     */
    function intl_date( $date, $format = null ) {
        if ( $format === null ) {
            $format = get_option( 'date_format' );
        }

        if ( $date instanceof DateTime ) {
            $timestamp = $date->getTimestamp();
        } else {
            $timestamp = strtotime( $date );
        }

        return date_i18n( $format, $timestamp );
    }

    /**
     *
     *
     * @param mixed   $body_classes
     * @return string
     */
    function body_class( $body_classes ) {
        ob_start();
        if ( is_array( $body_classes ) ) {
            $body_classes = explode( ' ', $body_classes );
        }
        body_class( $body_classes );
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * @param int|string $from
     * @param int|string $to
     * @param string $format_past
     * @param string $format_future
     * @return string
     */
    function time_ago( $from, $to = null, $format_past = '%s ago', $format_future = '%s from now' ) {
        $to = $to === null ? time() : $to;
        $to = is_int( $to ) ? $to : strtotime( $to );
        $from = is_int( $from ) ? $from : strtotime( $from );

        if ( $from < $to ) {
            return sprintf( $format_past, human_time_diff( $from, $to ) );
        } else {
            return sprintf( $format_future, human_time_diff( $to, $from ) );
        }
    }
}
