<?php

/**
 * @group called-post-constructor
 */
class TestTimberTwig extends Timber_UnitTestCase
{
    public function tear_down()
    {
        parent::tear_down();

        $lang_dir = get_stylesheet_directory() . '/languages';
        if (file_exists($lang_dir . '/en_US.po')) {
            unlink($lang_dir . '/en_US.po');
        }
        if (file_exists($lang_dir . '/en_US.mo')) {
            unlink($lang_dir . '/en_US.mo');
        }
    }

    public function testFormat()
    {
        $str = '{{ "I like %s and %s"|format(foo, "bar") }}';
        $return = Timber::compile_string($str, [
            'foo' => 'foo',
        ]);
        $this->assertEquals('I like foo and bar', $return);
    }

    public function testTranslate()
    {
        load_textdomain('timber-test', __DIR__ . '/languages/timber-test-en_US.mo');

        $str = "I like {{ __('thingy', 'timber-test') }}";
        $return = Timber::compile_string($str, [
            'foo' => 'foo',
        ]);
        $this->assertEquals('I like Cheesy Poofs', $return);

        $str = "I like {{ __('doobie', 'timber-test') }}";
        $return = Timber::compile_string($str, [
            'foo' => 'foo',
        ]);
        $this->assertEquals('I like doobie', $return);
    }

    public function testTranslateAndFormat()
    {
        load_textdomain('timber-test', __DIR__ . '/languages/timber-test-en_US.mo');

        $str = "You like {{__('%s', 'timber-test')|format('thingy')}}";
        $return = Timber::compile_string($str);
        $this->assertEquals('You like thingy', $return);
        $str = "You like {{__('%s'|format('thingy'), 'timber-test')}}";
        $return = Timber::compile_string($str);
        $this->assertEquals('You like Cheesy Poofs', $return);
    }

    public function testDoAction()
    {
        global $action_tally;
        global $php_unit;
        $php_unit = $this;
        $action_tally = [];

        $my_action_foo = function () {
            global $action_tally, $php_unit;
            $php_unit->assertTrue(true);
            $action_tally[] = 'my_action_foo';

            return 'foo';
        };

        $my_action_args = function ($bar) {
            global $action_tally, $php_unit;
            $php_unit->assertEquals('bar', $bar);
            $action_tally[] = 'my_action_args';

            return 'foo';
        };

        $timber_compile_done = function () {
            global $action_tally, $php_unit;

            $php_unit->assertContains('my_action_args', $action_tally);
            $php_unit->assertContains('my_action_foo', $action_tally);
        };

        $this->add_action_temporarily('my_action_foo', $my_action_foo);
        $this->add_action_temporarily('my_action_args', $my_action_args);
        $this->add_action_temporarily('timber/compile/done', $timber_compile_done);

        $str = Timber::compile('assets/test-do-action.twig');
        $str = trim($str);

        $this->assertEquals('Stuff', $str);
    }

    public function testWordPressPasswordFilters()
    {
        $post_id = $this->factory->post->create([
            'post_title' => 'My Private Post',
            'post_password' => 'abc123',
        ]);
        $context = [];
        add_filter('protected_title_format', function ($title) {
            return 'Protected: ' . $title;
        });
        $context['post'] = Timber::get_post($post_id);
        if (post_password_required($post_id)) {
            $this->assertTrue(true);
            $str = Timber::compile('assets/test-wp-filters.twig', $context);
            $this->assertEquals('Protected: My Private Post', trim($str));
        } else {
            $this->assertTrue(false, 'Something wrong with the post password reqd');
        }
    }

    public function testToArrayWithString()
    {
        $thing = 'thing';
        $str = '{% for thing in things|array %}{{thing}}{% endfor %}';
        $this->assertEquals('thing', Timber::compile_string($str, [
            'things' => $thing,
        ]));
    }

    public function testToArrayWithArray()
    {
        $thing = ['thing', 'thang'];
        $str = '{% for thing in things|array %}{{thing}}{% endfor %}';
        $this->assertEquals('thingthang', Timber::compile_string($str, [
            'things' => $thing,
        ]));
    }

    public function testTwigString()
    {
        $str = 'Foo';
        $arr = ['Bar', 'Quack'];
        $twig = '{{string|join}}x{{array|join("x")}}';
        $this->assertEquals('FooxBarxQuack', trim(Timber::compile_string($twig, [
            'string' => $str,
            'array' => $arr,
        ])));
    }

    /**
     * @expectedDeprecated {{ my_object | get_class }}
     */
    public function testFilterFunction()
    {
        $pid = $this->factory->post->create([
            'post_title' => 'Foo',
        ]);
        $post = Timber::get_post($pid);
        $str = 'I am a {{post | get_class }}';
        $this->assertEquals('I am a Timber\Post', Timber::compile_string($str, [
            'post' => $post,
        ]));
    }

    public function testFilterTruncate()
    {
        $gettysburg = 'Four score and seven years ago our fathers brought forth on this continent, a new nation, conceived in Liberty, and dedicated to the proposition that all men are created equal.';
        $str = Timber::compile_string("{{address | truncate(6)}}", [
            'address' => $gettysburg,
        ]);
        $this->assertEquals('Four score and seven years ago&hellip;', $str);
    }

    public function testFilterTrimCharacters()
    {
        $gettysburg = 'Four score and seven years ago our fathers brought forth on this continent, a new nation, conceived in Liberty, and dedicated to the proposition that all men are created equal.';
        $str = Timber::compile_string("{{content | excerpt_chars(100)}}", [
            'content' => $gettysburg,
        ]);
        $this->assertEquals('Four score and seven years ago our fathers brought forth on this continent, a new nation, co&hellip;', $str);
    }

    public function testSetSimple()
    {
        $result = Timber::compile('assets/set-simple.twig', [
            'foo' => 'bar',
        ]);
        $this->assertEquals('jiggy', trim($result));
    }

    public function testEscUrl()
    {
        $url = 'http://example.com/Mr WordPress';
        $str = Timber::compile_string("{{the_url | e('esc_url')}}", [
            'the_url' => $url,
        ]);
        $this->assertEquals('http://example.com/Mr%20WordPress', $str);
    }

    public function testWpKsesPost()
    {
        $evil_script = '<div foo="bar" src="bum">Foo</div><script>DoEvilThing();</script>';
        $str = Timber::compile_string("{{ evil_script | e('wp_kses_post') }}", [
            'evil_script' => $evil_script,
        ]);
        $this->assertEquals('<div>Foo</div>DoEvilThing();', $str);
    }

    public function testEscHtml()
    {
        // Simple string
        $html = "The quick brown fox.";

        $str = Timber::compile_string("{{text | e('esc_html')}}", [
            'text' => $html,
        ]);

        $this->assertEquals($html, $str);

        $escaped = "http://localhost/trunk/wp-login.php?action=logout&amp;_wpnonce=cd57d75985";

        $str = Timber::compile_string("{{text | e('esc_html')}}", [
            'text' => 'http://localhost/trunk/wp-login.php?action=logout&_wpnonce=cd57d75985',
        ]);

        $this->assertEquals($escaped, $str);

        // SQL query

        $escaped = "SELECT meta_key, meta_value FROM wp_trunk_sitemeta WHERE meta_key IN (&#039;site_name&#039;, &#039;siteurl&#039;, &#039;active_sitewide_plugins&#039;, &#039;_site_transient_timeout_theme_roots&#039;, &#039;_site_transient_theme_roots&#039;, &#039;site_admins&#039;, &#039;can_compress_scripts&#039;, &#039;global_terms_enabled&#039;) AND site_id = 1";

        $str = Timber::compile_string("{{text | e('esc_html')}}", [
            'text' => "SELECT meta_key, meta_value FROM wp_trunk_sitemeta WHERE meta_key IN ('site_name', 'siteurl', 'active_sitewide_plugins', '_site_transient_timeout_theme_roots', '_site_transient_theme_roots', 'site_admins', 'can_compress_scripts', 'global_terms_enabled') AND site_id = 1",
        ]);
        $this->assertEquals($escaped, $str);
    }

    public function testEscJs()
    {
        $escaped = 'foo &amp; bar &amp;baz; &nbsp;';
        $str = Timber::compile_string("{{text | e('esc_js')}}", [
            'text' => 'foo & bar &baz; &nbsp;',
        ]);

        $this->assertEquals($escaped, $str);

        $escaped = "foo \\' bar \\' baz &#x26;";
        $str = Timber::compile_string("{{text | e('esc_js')}}", [
            'text' => 'foo &#x27; bar &#39; baz &#x26;',
        ]);

        $this->assertEquals($escaped, $str);
    }

    public function testSetObject()
    {
        $this->expectException(Twig\Error\SyntaxError::class);
        $pid = $this->factory->post->create([
            'post_title' => 'Spaceballs',
        ]);
        $post = Timber::get_post($pid);
        $result = Timber::compile('assets/set-object.twig', [
            'post' => $post,
        ]);
        $this->assertEquals('Spaceballs: may the schwartz be with you', trim($result));
    }

    public function testTwigFunction()
    {
        $template = '{{bloginfo("name")}}';
        $result = Timber::compile_string($template);
        $this->assertEquals('Test Blog', $result);
    }

    public function testAddToTwig()
    {
        add_filter('timber/twig', function ($twig) {
            $twig->addFilter(new Twig\TwigFilter('foobar', function ($text) {
                return $text . 'foobar';
            }));
            return $twig;
        });
        $str = Timber::compile_string('{{ "jared" | foobar }}');
        $this->assertEquals('jaredfoobar', $str);
    }

    public function testTimberTwigObjectFilter()
    {
        add_filter('timber/twig', function ($twig) {
            $twig->addFilter(new Twig\TwigFilter('quack', function ($text) {
                return $text . ' Quack!';
            }));
            return $twig;
        });
        $str = Timber::compile_string('{{ "jared" | quack }}');
        $this->assertEquals('jared Quack!', $str);
    }

    public function testTwigShortcode()
    {
        add_shortcode('my_shortcode', function ($atts, $content) {
            return 'Jaredfoo';
        });
        $str = Timber::compile_string('{{shortcode("[my_shortcode]")}}');
        $this->assertEquals('Jaredfoo', $str);
    }

    public function testTwigShortcodeWithContent()
    {
        add_shortcode('duck', function ($atts, $content) {
            return $content . ' says quack!';
        });

        $str = Timber::compile_string('{{shortcode("[duck]Lauren[/duck]")}}');
        $this->assertEquals('Lauren says quack!', $str);
    }

    /**
     * @expectedDeprecated Timber::$autoescape
     */
    public function testAutoescapeVariableDeprecated()
    {
        Timber::$autoescape = true;

        $str = Timber\Timber::compile_string('The {{ region }} remembers…', [
            'region' => '<strong>North</strong>',
        ]);

        $this->assertEquals(
            'The &lt;strong&gt;North&lt;/strong&gt; remembers…',
            $str
        );

        Timber::$autoescape = false;
    }

    public function testAutoescapeTrueBackwardsCompatibilityWithFilter()
    {
        $autoescape_filter = function ($options) {
            $options['autoescape'] = true;

            return $options;
        };

        add_filter('timber/twig/environment/options', $autoescape_filter);

        $str = Timber\Timber::compile_string('The {{ region }} remembers…', [
            'region' => '<strong>North</strong>',
        ]);

        remove_filter('timber/twig/environment/options', $autoescape_filter);

        $this->assertEquals(
            'The &lt;strong&gt;North&lt;/strong&gt; remembers…',
            $str
        );
    }

    public function testAutoescapeStrategyWithFilter()
    {
        $autoescape_filter = function ($options) {
            $options['autoescape'] = 'html';

            return $options;
        };

        add_filter('timber/twig/environment/options', $autoescape_filter);

        $str = Timber\Timber::compile_string('The {{ region }} remembers…', [
            'region' => '<strong>North</strong>',
        ]);

        remove_filter('timber/twig/environment/options', $autoescape_filter);

        $this->assertEquals(
            'The &lt;strong&gt;North&lt;/strong&gt; remembers…',
            $str
        );
    }

    public function testRemoveADefaultFunction()
    {
        $this->expectException(Twig\Error\SyntaxError::class);
        add_filter('timber/twig/functions', function ($functions) {
            unset($functions['shortcode']);
            return $functions;
        });
        Timber::compile_string("{{ text|shortcode }}", [
            'text' => 'A function has been removed',
        ]);
    }

    public function testRemoveADefaultFilter()
    {
        $this->expectException(Twig\Error\SyntaxError::class);
        add_filter('timber/twig/filters', function ($filters) {
            unset($filters['wpautop']);
            return $filters;
        });
        Timber::compile_string("{{ text|wpautop }}", [
            'text' => 'A filter has been removed',
        ]);
    }
}
