<?php

	class TimberTermTwig extends WP_UnitTestCase {

		function testDoAction(){
			global $action_tally;
			global $php_unit;
			$php_unit = $this;
			$action_tally = array();
			add_action('my_action_foo', function(){
				global $action_tally, $php_unit;
				$php_unit->assertTrue(true);
				$action_tally[] = 'my_action_foo';
				return 'foo';
			});
			add_action('my_action_args', function($bar){
				global $action_tally, $php_unit;
				$php_unit->assertEquals('bar', $bar);
				$action_tally[] = 'my_action_args';
				return 'foo';
			});
			add_action('timber_compile_done', function(){
				global $action_tally, $php_unit;
				$php_unit->assertContains('my_action_args', $action_tally);
				$php_unit->assertContains('my_action_foo', $action_tally);
			});
			$str = Timber::compile('assets/test-do-action.twig');
			$str = trim($str);
			$this->assertEquals('Stuff', $str);
		}

		function testDoActionContext(){
			global $php_unit;
			$php_unit = $this;
			global $action_context_tally;
			$action_context_tally = array();
			add_action('my_action_context_vars', function($foo, $bar, $context) {
				global $php_unit;
				$php_unit->assertEquals('foo', $foo);
				$php_unit->assertEquals('bar', $bar);
				$php_unit->assertEquals('Jaredz Post', $context['post']->post_title);
				global $action_context_tally;
				$action_context_tally[] = 'my_action_context_vars';
			}, 10, 3);

			add_action('my_action_context_var', function($foo, $context) {
				global $php_unit;
				$php_unit->assertEquals('foo', $foo);
				$php_unit->assertEquals('Jaredz Post', $context['post']->post_title);
				global $action_context_tally;
				$action_context_tally[] = 'my_action_context_vars';
			}, 10, 2);

			add_action('my_action_context', function($context){
				global $php_unit;
				$php_unit->assertEquals('Jaredz Post', $context['post']->post_title);
				global $action_context_tally;
				$action_context_tally[] = 'my_action_context';
			});

			add_action('timber_compile_done', function(){
				global $php_unit;
				global $action_context_tally;
				$php_unit->assertContains('my_action_context_vars', $action_context_tally);
				$php_unit->assertContains('my_action_context', $action_context_tally);
			});
			$post_id = $this->factory->post->create(array('post_title' => "Jaredz Post", 'post_content' => 'stuff to say'));
			$context['post'] = new TimberPost($post_id);
			$str = Timber::compile('assets/test-action-context.twig', $context);
			$this->assertEquals('Here: stuff to say', trim($str));
		}

		function testWordPressPasswordFilters(){
			$post_id = $this->factory->post->create(array('post_title' => 'My Private Post', 'post_password' => 'abc123'));
			$context = array();
			add_filter('protected_title_format', function($title){
				return 'Protected: '.$title;
			});
			$context['post'] = new TimberPost($post_id);
			if (post_password_required($post_id)){
				$this->assertTrue(true);
				$str = Timber::compile('assets/test-wp-filters.twig', $context);
				$this->assertEquals('Protected: My Private Post', trim($str));
			} else {
				$this->assertTrue(false, 'Something wrong with the post password reqd');
			}
		}

		function testTimberPostInTwig(){
			$pid = $this->factory->post->create(array('post_title' => 'Foo'));
			$str = '{{TimberPost('.$pid.').title}}';
			$this->assertEquals('Foo', Timber::compile_string($str));
		}

		function testTimberPostsInTwig(){
			$pids[] = $this->factory->post->create(array('post_title' => 'Foo'));
			$pids[] = $this->factory->post->create(array('post_title' => 'Bar'));
			$str = '{% for post in TimberPost(pids) %}{{post.title}}{% endfor %}';
			$this->assertEquals('FooBar', Timber::compile_string($str, array('pids' => $pids)));
		}

		function testTimberUserInTwig(){
			$uid = $this->factory->user->create(array('display_name' => 'Pete Karl'));
			$str = '{{TimberUser('.$uid.').name}}';
			$this->assertEquals('Pete Karl', Timber::compile_string($str));
		}

		function testFilterFunction() {
			$pid = $this->factory->post->create(array('post_title' => 'Foo'));
			$post = new TimberPost( $pid );
			$str = 'I am a {{post | get_class }}';
			$this->assertEquals('I am a TimberPost', Timber::compile_string($str, array('post' => $post)));
		}
	}
