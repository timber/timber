<?php

	class TimberTermTwigFilters extends WP_UnitTestCase {

		function testTwigFitlerSanitize(){
			$data['title'] = "Jared's Big Adventure";
			$str = Timber::compile_string('{{title|sanitize}}', $data);
			$this->assertEquals('jareds-big-adventure', $str);
		}

		function testTwigFilterDate(){
			$date = '1983-09-28';
			$data['bday'] = $date;
			$str = Timber::compile_string("{{bday|date('M j, Y')}}", $data);
			$this->assertEquals('Sep 28, 1983', trim($str));
		}

		function testTwigFilterDateWordPressFormat(){
			$data['day'] = '2012-10-15 20:14:48';
			$str = Timber::compile_string("{{day|date('M jS, Y g:ia')}}", $data);
			$this->assertEquals('Oct 15th, 2012 8:14pm', trim($str));
		}
	}
