<?php

	class TimberTermTwig extends WP_UnitTestCase {

		function testDoAction(){
			global $action_tally;
			$action_tally = array();
			add_action('my_action_foo', function(){
				global $action_tally;
				$this->assertTrue(true);
				$action_tally[] = 'my_action_foo';
				return 'foo';
			});
			add_action('my_action_args', function($bar){
				global $action_tally;
				$this->assertEquals('bar', $bar);
				$action_tally[] = 'my_action_args';
				return 'foo';
			});
			add_action('timber_compile_done', function(){
				global $action_tally;
				$this->assertContains('my_action_args', $action_tally);
				$this->assertContains('my_action_foo', $action_tally);
			});
			$str = Timber::compile('assets/test-do-action.twig');
			$str = trim($str);
			$this->assertEquals('Stuff', $str);
		}

		function testDoActionContext(){
			global $action_context_tally;
			$action_context_tally = array();
			add_action('my_action_context_vars', function($foo, $context) {
				$this->assertEquals('foo', $foo);
				$this->assertEquals('Jaredz Post', $context['post']->post_title);
				global $action_context_tally;
				$action_context_tally[] = 'my_action_context_vars';
			}, 10, 2);

			add_action('my_action_context', function($context){
				$this->assertEquals('Jaredz Post', $context['post']->post_title);
				global $action_context_tally;
				$action_context_tally[] = 'my_action_context';
			});

			add_action('timber_compile_done', function(){
				global $action_context_tally;
				$this->assertContains('my_action_context_vars', $action_context_tally);
				$this->assertContains('my_action_context', $action_context_tally);
			});
			$post_id = $this->factory->post->create(array('post_title' => "Jaredz Post", 'post_content' => 'stuff to say'));
			$context['post'] = new TimberPost($post_id);
			$str = Timber::compile('assets/test-action-context.twig', $context);
			$this->assertEquals('Here: stuff to say', trim($str));
		}
	}
