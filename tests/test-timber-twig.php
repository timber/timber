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

		function installTranlsationFiles( $lang_dir ) {
			if( !is_dir($lang_dir) ) {
				mkdir($lang_dir , 0777, true);
			}
			copy( __DIR__.'/assets/languages/en_US.po', $lang_dir.'/en_US.po' );
			copy( __DIR__.'/assets/languages/en_US.mo', $lang_dir.'/en_US.mo' );
			return true;
		}

		function _setupTranslationFiles() {
			$lang_dir = get_stylesheet_directory().'/languages';
			
			if ( !file_exists($lang_dir.'/en_US.po') ) {
				$this->installTranlsationFiles($lang_dir);
			}

			$td = 'timber_test_theme';
			load_theme_textdomain($td, $lang_dir);

			return $td;
		}

		function testFormat() {
			$str = '{{ "I like %s and %s"|format(foo, "bar") }}';
			$return = Timber::compile_string($str, array('foo' => 'foo'));
			$this->assertEquals('I like foo and bar', $return);
		}

		function testTranslate() {
			$td = $this->_setupTranslationFiles();
			$str = "I like {{ __('thingy', '$td') }}";
			$return = Timber::compile_string($str, array('foo' => 'foo'));
			$this->assertEquals('I like Cheesy Poofs', $return);

			$str = "I like {{ __('doobie', '$td') }}";
			$return = Timber::compile_string($str, array('foo' => 'foo'));
			$this->assertEquals('I like doobie', $return);
		}

		function testTranslateAndFormat() {
			$td = $this->_setupTranslationFiles();

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

		function testFilterTrimCharacters() {
			$gettysburg = 'Four score and seven years ago our fathers brought forth on this continent, a new nation, conceived in Liberty, and dedicated to the proposition that all men are created equal.';
			$str = Timber::compile_string("{{content | excerpt_chars(100)}}", array('content' => $gettysburg));
			$this->assertEquals('Four score and seven years ago our fathers brought forth on this continent, a new nation, co&hellip;', $str);
		}

		function testSetSimple() {
			$result = Timber::compile('assets/set-simple.twig', array('foo' => 'bar'));
			$this->assertEquals('jiggy', trim($result));
		}

		function testEscUrl(){
			$url = 'http://example.com/Mr WordPress';
			$str = Timber::compile_string( "{{the_url | e('esc_url')}}", array( 'the_url' => $url ) );
			$this->assertEquals( 'http://example.com/Mr%20WordPress', $str );

		}

		function testWpKsesPost(){

			$evil_script = '<div foo="bar" src="bum">Foo</div><script>DoEvilThing();</script>';
			$str         = Timber::compile_string( "{{ evil_script | e('wp_kses_post') }}", array( 'evil_script' => $evil_script ) );
			$this->assertEquals( '<div>Foo</div>DoEvilThing();', $str );
		}

		function testEscHtml(){

			// Simple string
			$html = "The quick brown fox.";

			$str = Timber::compile_string( "{{text | e('esc_html')}}", array( 'text' => $html ) );

			$this->assertEquals( $html, $str );


			$escaped = "http://localhost/trunk/wp-login.php?action=logout&amp;_wpnonce=cd57d75985";

			$str = Timber::compile_string( "{{text | e('esc_html')}}", array( 'text' => 'http://localhost/trunk/wp-login.php?action=logout&_wpnonce=cd57d75985' ) );

			$this->assertEquals( $escaped, $str );

			// SQL query

			$escaped = "SELECT meta_key, meta_value FROM wp_trunk_sitemeta WHERE meta_key IN (&#039;site_name&#039;, &#039;siteurl&#039;, &#039;active_sitewide_plugins&#039;, &#039;_site_transient_timeout_theme_roots&#039;, &#039;_site_transient_theme_roots&#039;, &#039;site_admins&#039;, &#039;can_compress_scripts&#039;, &#039;global_terms_enabled&#039;) AND site_id = 1";

			$str = Timber::compile_string( "{{text | e('esc_html')}}", array( 'text' =>"SELECT meta_key, meta_value FROM wp_trunk_sitemeta WHERE meta_key IN ('site_name', 'siteurl', 'active_sitewide_plugins', '_site_transient_timeout_theme_roots', '_site_transient_theme_roots', 'site_admins', 'can_compress_scripts', 'global_terms_enabled') AND site_id = 1"));
			$this->assertEquals( $escaped, $str );

		}

		function testEscJs(){
			$escaped = 'foo &amp; bar &amp;baz; &nbsp;';
			$str = Timber::compile_string( "{{text | e('esc_js')}}", array( 'text' => 'foo & bar &baz; &nbsp;' ) );

			$this->assertEquals($escaped, $str);

			$escaped = "foo \\' bar \\' baz &#x26;";
			$str = Timber::compile_string( "{{text | e('esc_js')}}", array( 'text' => 'foo &#x27; bar &#39; baz &#x26;' ) );

			$this->assertEquals($escaped, $str);

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

		function testTwigFunction() {
			$template = '{{bloginfo("name")}}';
			$result = Timber::compile_string($template);
			$this->assertEquals('Test Blog', $result);
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
