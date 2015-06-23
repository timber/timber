<?php

    class TimberDatesTest extends WP_UnitTestCase
    {
        public function testDate()
        {
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $twig = 'I am from {{post.date}}';
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I am from '.date('F j, Y'), $str);
        }

        public function testPostDisplayDate()
        {
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $twig = 'I am from {{post.display_date}}';
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I am from '.date('F j, Y'), $str);
        }

        public function testPostDate()
        {
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $twig = 'I am from {{post.post_date}}';
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I am from '.$post->post_date, $str);
        }

        public function testPostDateWithFilter()
        {
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $twig = 'I am from {{post.post_date|date}}';
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I am from '.date('F j, Y'), $str);
        }

        public function testModifiedDate()
        {
            $date = date('F j, Y @ g:i a');
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $twig = "I was modified {{post.modified_date('F j, Y @ g:i a')}}";
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I was modified '.$date, $str);
        }

        public function testModifiedDateFilter()
        {
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            add_filter('get_the_modified_date', function ($the_date) {
                return 'foobar';
            });
            $twig = "I was modified {{post.modified_date('F j, Y @ g:i a')}}";
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I was modified foobar', $str);
        }

        public function testModifiedTime()
        {
            $date = date('F j, Y @ g:i a');
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            $twig = "I was modified {{post.modified_time('F j, Y @ g:i a')}}";
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I was modified '.$date, $str);
        }

        public function testModifiedTimeFilter()
        {
            $pid = $this->factory->post->create();
            $post = new TimberPost($pid);
            add_filter('get_the_modified_time', function ($the_date) {
                return 'foobar';
            });
            $twig = "I was modified {{post.modified_time('F j, Y @ g:i a')}}";
            $str = Timber::compile_string($twig, array('post' => $post));
            $this->assertEquals('I was modified foobar', $str);
        }
    }
