<?php

	class TestTimberDates extends Timber_UnitTestCase {

		function testDate(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'I am from {{post.date}}';
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I am from '.date('F j, Y'), $str);
		}

		function testTime(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'Posted at {{post.time}}';
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('Posted at '.date('g:i a'), $str);
		}

		function testPostDisplayDate(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'I am from {{post.date}}';
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I am from '.date('F j, Y'), $str);
		}

		function testPostDate(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'I am from {{post.post_date}}';
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I am from '.$post->post_date, $str);
		}

		function testPostDateWithFilter(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'I am from {{post.post_date|date}}';
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I am from '.date('F j, Y'), $str);
		}

		function testModifiedDate(){
			$date = date('F j, Y @ g:i a');
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = "I was modified {{post.modified_date('F j, Y @ g:i a')}}";
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I was modified '.$date, $str);
		}

		function testModifiedDateFilter() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			add_filter('get_the_modified_date', function($the_date) {
				return 'foobar';
			});
			$twig = "I was modified {{post.modified_date('F j, Y @ g:i a')}}";
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I was modified foobar', $str);
		}

		function testModifiedTime(){
			$date = date('F j, Y @ g:i a');
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = "I was modified {{post.modified_time('F j, Y @ g:i a')}}";
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I was modified '.$date, $str);
		}

		function testInternationalTime(){
			$date = new DateTime('2015-09-28 05:00:00', new DateTimeZone('europe/amsterdam'));
			$twig = "{{'" . $date->format('g:i') . "'|date('g:i')}}";
			$str = Timber::compile_string($twig);
			$this->assertEquals('5:00', $str);
		}

		function testModifiedTimeFilter() {
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			add_filter('get_the_modified_time', function($the_date) {
				return 'foobar';
			});
			$twig = "I was modified {{post.modified_time('F j, Y @ g:i a')}}";
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I was modified foobar', $str);
		}

		function testACFDate() {
			$twig = "Thing is on {{'20150928'|date('M j, Y')}}";
			$str = Timber::compile_string($twig);
			$this->assertEquals('Thing is on Sep 28, 2015', $str);
		}

		function testUnixDate() {
			$twig = "Thing is on {{'1446127859'|date('M j, Y')}}";
			$str = Timber::compile_string($twig);
			$this->assertEquals('Thing is on Oct 29, 2015', $str);
		}

	}
