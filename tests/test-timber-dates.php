<?php

	class TimberDatesTest extends WP_UnitTestCase {

		function testDate(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'I am from {{post.date}}';
			$str = Timber::compile_string($twig, array('post' => $post));
			$this->assertEquals('I am from '.date('F j, Y'), $str);
		}

		function testPostDisplayDate(){
			$pid = $this->factory->post->create();
			$post = new TimberPost($pid);
			$twig = 'I am from {{post.display_date}}';
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

	}