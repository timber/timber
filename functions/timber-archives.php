<?php
class TimberArchives extends TimberCore {

	var $base = '';

	function __construct($args, $base = ''){
		$this->init($args, $base);
	}

    /**
     * @param array|string $args
     * @param string $base
     */
    function init($args, $base = ''){
		$this->base = $base;
		$this->items = $this->get_items($args);
	}

    /**
     * @param string $url
     * @param string $text
     * @return mixed
     */
    function get_archives_link($url, $text) {
		$ret['text'] = $ret['title'] = $ret['name'] = wptexturize($text);
		$ret['url'] = $ret['link'] = esc_url(TimberURLHelper::prepend_to_url($url, $this->base));
		return $ret;
	}

    /**
     * @param array|string $args
     * @param string $last_changed
     * @param string $join
     * @param string $where
     * @param string $order
     * @param string $limit
     * @return array
     */
    function get_items_yearly($args, $last_changed, $join, $where, $order, $limit){
		global $wpdb;
		$output = array();
		$query = "SELECT YEAR(post_date) AS `year`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			foreach ( (array) $results as $result) {
				$url = get_year_link($result->year);
				$text = sprintf('%d', $result->year);
				$output[] = $this->get_archives_link($url, $text);
			}
		}
		return $output;
	}

    /**
     * @param array|string $args
     * @param string $last_changed
     * @param string $join
     * @param string $where
     * @param string $order
     * @param string $limit
     * @param bool $nested
     * @return array
     */
    function get_items_montly($args, $last_changed, $join, $where, $order, $limit, $nested = true){
		global $wpdb, $wp_locale;
		$output = array();
		$defaults = array(
			'show_year' => true,
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );
		$where = $where;
		//will need to specify which year we're looking for
		$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date) ORDER BY post_date $order $limit";
		$key = md5( $query );
		$key = "wp_get_archives:$key:$last_changed";
		if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
			$results = $wpdb->get_results( $query );
			wp_cache_set( $key, $results, 'posts' );
		}
		if ( $results ) {
			foreach ( (array) $results as $result ) {
				$url = get_month_link( $result->year, $result->month );
				/* translators: 1: month name, 2: 4-digit year */

				if ($show_year && !$nested){
					$text = sprintf(__('%1$s %2$d'), $wp_locale->get_month($result->month), $result->year);
				} else {
					$text = sprintf(__('%1$s'), $wp_locale->get_month($result->month));
				}
				if ($nested){
					$output[$result->year][] = $this->get_archives_link($url, $text);
				} else {
					$output[] = $this->get_archives_link($url, $text);
				}
			}
		}
		if ($nested){
			$out2 = array();
			foreach($output as $year=>$months){
				$out2[] = array('name' => $year, 'children' => $months);
			}
			return $out2;
		}
		return $output;
	}

    /**
     * @param array|string $args
     * @return array|string
     */
    function get_items($args){
		global $wpdb, $wp_locale;

		$defaults = array(
			'type' => 'monthly', 'limit' => '',
			'format' => 'html', 'before' => '',
			'after' => '', 'show_post_count' => false,
			'order' => 'DESC',
			'post_type' => 'post',
			'nested' => true
		);

		$r = wp_parse_args( $args, $defaults );
		extract( $r, EXTR_SKIP );

		if ( '' == $type )
			$type = 'monthly';

		if ( '' != $limit ) {
			$limit = absint($limit);
			$limit = ' LIMIT '.$limit;
		}

		$order = strtoupper( $order );
		if ( $order !== 'ASC' ){
			$order = 'DESC';
		}

		// this is what will separate dates on weekly archive links
		$archive_week_separator = '&#8211;';

		// over-ride general date format ? 0 = no: use the date format set in Options, 1 = yes: over-ride
		$archive_date_format_over_ride = 0;

		// options for daily archive (only if you over-ride the general date format)
		$archive_day_date_format = 'Y/m/d';

		// options for weekly archive (only if you over-ride the general date format)
		$archive_week_start_date_format = 'Y/m/d';
		$archive_week_end_date_format	= 'Y/m/d';

		if ( !$archive_date_format_over_ride ) {
			$archive_day_date_format = get_option('date_format');
			$archive_week_start_date_format = get_option('date_format');
			$archive_week_end_date_format = get_option('date_format');
		}

		$where = apply_filters( 'getarchives_where', "WHERE post_type = '".$post_type."' AND post_status = 'publish'", $r );
		$join = apply_filters( 'getarchives_join', '', $r );

		$output = array();

		$last_changed = wp_cache_get( 'last_changed', 'posts' );
		if ( ! $last_changed ) {
			$last_changed = microtime();
			wp_cache_set( 'last_changed', $last_changed, 'posts' );
		}

		if ( 'monthly' == $type ) {
			$output = $this->get_items_montly($args, $last_changed, $join, $where, $order, $limit, $nested);
		} elseif ('yearly' == $type) {
			$output = $this->get_items_yearly($args, $last_changed, $join, $where, $order, $limit);
		} elseif ( 'yearlymonthly' == $type || 'yearmonth' == $type){
			$years = $this->get_items_yearly($args, $last_changed, $join, $where, $order, $limit);
			foreach($years as &$year){
				$args = array('show_year' => false);
				$year['children'] = $this->get_items_montly($args, $last_changed, $join, $where, $order, $limit);
			}
			$output = $years;
		} elseif ( 'daily' == $type ) {
			$query = "SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, DAYOFMONTH(post_date) AS `dayofmonth`, count(ID) as posts FROM $wpdb->posts $join $where GROUP BY YEAR(post_date), MONTH(post_date), DAYOFMONTH(post_date) ORDER BY post_date $order $limit";
			$key = md5( $query );
			$key = "wp_get_archives:$key:$last_changed";
			if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
				$results = $wpdb->get_results( $query );
				$cache[ $key ] = $results;
				wp_cache_set( $key, $results, 'posts' );
			}
			if ( $results ) {
				$afterafter = $after;
				foreach ( (array) $results as $result ) {
					$url	= get_day_link($result->year, $result->month, $result->dayofmonth);
					$date = sprintf('%1$d-%2$02d-%3$02d 00:00:00', $result->year, $result->month, $result->dayofmonth);
					$text = mysql2date($archive_day_date_format, $date);
					$output[] = $this->get_archives_link($url, $text);
				}
			}
		} elseif ( 'weekly' == $type ) {
			$week = _wp_mysql_week( '`post_date`' );
			$query = "SELECT DISTINCT $week AS `week`, YEAR( `post_date` ) AS `yr`, DATE_FORMAT( `post_date`, '%Y-%m-%d' ) AS `yyyymmdd`, count( `ID` ) AS `posts` FROM `$wpdb->posts` $join $where GROUP BY $week, YEAR( `post_date` ) ORDER BY `post_date` $order $limit";
			$key = md5( $query );
			$key = "wp_get_archives:$key:$last_changed";
			if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
				$results = $wpdb->get_results( $query );
				wp_cache_set( $key, $results, 'posts' );
			}
			$arc_w_last = '';
			$afterafter = $after;
			if ( $results ) {
					foreach ( (array) $results as $result ) {
						if ( $result->week != $arc_w_last ) {
							$arc_year = $result->yr;
							$arc_w_last = $result->week;
							$arc_week = get_weekstartend($result->yyyymmdd, get_option('start_of_week'));
							$arc_week_start = date_i18n($archive_week_start_date_format, $arc_week['start']);
							$arc_week_end = date_i18n($archive_week_end_date_format, $arc_week['end']);
							$url  = sprintf('%1$s/%2$s%3$sm%4$s%5$s%6$sw%7$s%8$d', home_url(), '', '?', '=', $arc_year, '&amp;', '=', $result->week);
							$text = $arc_week_start . $archive_week_separator . $arc_week_end;
							$output[] = $this->get_archives_link($url, $text);
						}
					}
			}
		} elseif ( ( 'postbypost' == $type ) || ('alpha' == $type) ) {
			$orderby = ('alpha' == $type) ? 'post_title ASC ' : 'post_date DESC ';
			$query = "SELECT * FROM $wpdb->posts $join $where ORDER BY $orderby $limit";
			$key = md5( $query );
			$key = "wp_get_archives:$key:$last_changed";
			if ( ! $results = wp_cache_get( $key, 'posts' ) ) {
				$results = $wpdb->get_results( $query );
				wp_cache_set( $key, $results, 'posts' );
			}
			if ( $results ) {
				foreach ( (array) $results as $result ) {
					if ( $result->post_date != '0000-00-00 00:00:00' ) {
						$url  = get_permalink( $result );
						if ( $result->post_title ) {
							/** This filter is documented in wp-includes/post-template.php */
							$text = strip_tags( apply_filters( 'the_title', $result->post_title, $result->ID ) );
						} else {
							$text = $result->ID;
						}
						$output .= get_archives_link($url, $text, $format, $before, $after);
					}
				}
			}
		}
		return $output;
	}
}
