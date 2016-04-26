<?php

	class TestTimberTwig extends Timber_UnitTestCase {

		function tearDown() {
			$lang_dir = get_stylesheet_directory().'/languages';
			if (file_exists($lang_dir.'/en_US.po' )) {
				unlink($lang_dir.'/en_US.po');
			}
			if (file_exists($lang_dir.'/en_US.mo' )) {
				unlink($lang_dir.'/en_US.mo');
			}
		}

		function _setupTranslationFiles() {
			$lang_dir = get_stylesheet_directory().'/languages';
			if (file_exists($lang_dir.'/en_US.po' )) {
				return;
			}
			if(!is_dir($lang_dir)) {
				mkdir($lang_dir , 0777);
        	}
			copy( __DIR__.'/assets/languages/en_US.po', $lang_dir.'/en_US.po' );
			copy( __DIR__.'/assets/languages/en_US.mo', $lang_dir.'/en_US.mo' );
			$theme = wp_get_theme();
			$td = $theme->get('TextDomain');
			load_theme_textdomain($td, $lang_dir);
		}

		function testFormat() {
			$str = '{{ "I like %s and %s"|format(foo, "bar") }}';
			$return = Timber::compile_string($str, array('foo' => 'foo'));
			$this->assertEquals('I like foo and bar', $return);
		}

		function testTranslate() {
			$this->_setupTranslationFiles();

			$theme = wp_get_theme();
			$td = $theme->get('TextDomain');
			$str = "I like {{ __('thingy', '$td')}}";
			$return = Timber::compile_string($str, array('foo' => 'foo'));
			$this->assertEquals('I like Cheesy Poofs', $return);

			$str = "I like {{ __('doobie', '$td')}}";
			$return = Timber::compile_string($str, array('foo' => 'foo'));
			$this->assertEquals('I like doobie', $return);
		}

		function testTranslateAndFormat() {
			$this->_setupTranslationFiles();
			sleep(1);
			$theme = wp_get_theme();
			$td = $theme->get('TextDomain');

			$str = "You like {{__('%s', '$td')|format('thingy')}}";
			$return = Timber::compile_string($str);
			$this->assertEquals('You like thingy', $return);

			$str = "You like {{__('%s'|format('thingy'), '$td')}}";
			$return = Timber::compile_string($str);
			$this->assertEquals('You like Cheesy Poofs', $return);

		}

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

		function testToArrayWithString() {
			$thing = 'thing';
			$str = '{% for thing in things|array %}{{thing}}{% endfor %}';
			$this->assertEquals('thing', Timber::compile_string($str, array('things' => $thing)));
		}

		function testToArrayWithArray() {
			$thing = array('thing', 'thang');
			$str = '{% for thing in things|array %}{{thing}}{% endfor %}';
			$this->assertEquals('thingthang', Timber::compile_string($str, array('things' => $thing)));
		}

		function testTwigString() {
			$str = 'Foo';
			$arr = array('Bar', 'Quack');
			$twig = '{{string|join}}x{{array|join("x")}}';
			$this->assertEquals('FooxBarxQuack', trim(Timber::compile_string($twig, array('string' => $str, 'array' => $arr))));
		}

		function testFilterFunction() {
			$pid = $this->factory->post->create(array('post_title' => 'Foo'));
			$post = new TimberPost( $pid );
			$str = 'I am a {{post | get_class }}';
			$this->assertEquals('I am a Timber\Post', Timber::compile_string($str, array('post' => $post)));
		}

		function testFilterTruncate() {
			$gettysburg = 'Four score and seven years ago our fathers brought forth on this continent, a new nation, conceived in Liberty, and dedicated to the proposition that all men are created equal.';
			$str = Timber::compile_string("{{address | truncate(6)}}", array('address' => $gettysburg));
			$this->assertEquals('Four score and seven years ago&hellip;', $str);
		}

		function testSetSimple() {
			$result = Timber::compile('assets/set-simple.twig', array('foo' => 'bar'));
			$this->assertEquals('jiggy', trim($result));
		}

		/**
     	* @expectedException Twig_Error_Syntax
     	*/
		function testSetObject() {
			$pid = $this->factory->post->create(array('post_title' => 'Spaceballs'));
			$post = new TimberPost( $pid );
			$result = Timber::compile('assets/set-object.twig', array('post' => $post));
			$this->assertEquals('Spaceballs: may the schwartz be with you', trim($result));
		}

		function testAddToTwig() {
			add_filter('get_twig', function( $twig ) {
				$twig->addFilter( new Twig_SimpleFilter( 'foobar', function( $text ) {
					return $text . 'foobar';
				}) );
				return $twig;
			});
			$str = Timber::compile_string('{{ "jared" | foobar }}');
			$this->assertEquals( 'jaredfoobar' , $str );
		}

		function testTimberTwigObjectFilter() {
			add_filter('timber/twig', function( $twig ) {
				$twig->addFilter( new Twig_SimpleFilter( 'quack', function( $text ) {
					return $text . ' Quack!';
				}) );
				return $twig;
			});
			$str = Timber::compile_string('{{ "jared" | quack }}');
			$this->assertEquals( 'jared Quack!' , $str );
		}

		function testTwigShortcode() {
			add_shortcode('my_shortcode', function( $atts, $content ) {
				return 'Jaredfoo';
			});
			$str = Timber::compile_string('{{shortcode("[my_shortcode]")}}');
			$this->assertEquals('Jaredfoo', $str);
		}

		function testTwigShortcodeWithContent() {
			add_shortcode('duck', function( $atts, $content ) {
				return $content . ' says quack!';
			});

			$str = Timber::compile_string('{{shortcode("[duck]Lauren[/duck]")}}');
			$this->assertEquals('Lauren says quack!', $str);

		}


	}
