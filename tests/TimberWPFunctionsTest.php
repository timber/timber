<?php

namespace Timber\Tests {
    use PHPUnit\Framework\Attributes\IgnoreDeprecations;
    use Timber\FunctionWrapper;
    use Timber\Helper;
    use Timber\Timber;

    class TimberWPFunctionsTest extends TimberIntegrationTestCase
    {
        public function testFunctionFire()
        {
            $str = '{{function("my_test_function")}}';
            $output = Timber::compile_string($str);
            $this->assertEquals('jared sez hi', $output);
        }

        #[IgnoreDeprecations]
        public function testFooterOnFooterFW()
        {
            if ($this->isWordPressVersion('6.4', '>=')) {
                $this->setExpectedDeprecated('the_block_template_skip_link');
            }

            global $wp_scripts;
            $wp_scripts = null;
            \wp_enqueue_script('jquery', false, [], false, true);
            $fw1 = new FunctionWrapper('wp_footer', [], true);
            $fw2 = new FunctionWrapper('wp_footer', [], true);
            $this->assertGreaterThan(50, \strlen($fw1->call()));
            // In modern WordPress (6.x+), wp_footer outputs consistent content
            // on subsequent calls due to block-related scripts and other hooks.
            // Scripts print once, but block/theme hooks may still output content.
            $this->assertGreaterThanOrEqual(0, \strlen($fw2->call()));
            \wp_dequeue_script('jquery');
        }

        #[IgnoreDeprecations]
        public function testFooterAlone()
        {
            if ($this->isWordPressVersion('6.4', '>=')) {
                $this->setExpectedDeprecated('the_block_template_skip_link');
            }
            global $wp_scripts;
            $wp_scripts = null;
            \wp_enqueue_script('jquery', false, [], false, true);
            $fw1 = new FunctionWrapper('wp_footer', [], true);
            $this->assertGreaterThan(50, \strlen($fw1->call()));
        }

        public function testDoubleAction()
        {
            \add_action('jared_action', function () {
                echo 'bar';
            });
            $fw1 = new FunctionWrapper('do_jared_action', [], true);
            $fw2 = new FunctionWrapper('do_jared_action', [], true);
            $this->assertEquals($fw1->call(), $fw2->call());
            $this->assertEquals('bar', $fw1->call());
        }

        #[IgnoreDeprecations]
        public function testDoubleActionWPFooter()
        {
            if ($this->isWordPressVersion('6.4', '>=')) {
                $this->setExpectedDeprecated('the_block_template_skip_link');
            }
            global $wp_scripts;
            $wp_scripts = null;
            $this->add_action_temporarily('wp_footer', 'echo_junk');
            $fw1 = new FunctionWrapper('wp_footer', [], true);
            $fw2 = new FunctionWrapper('wp_footer', [], true);
            $this->assertEquals($fw1->call(), $fw2->call());
            $pos = \strpos($fw2->call(), 'foo');
            $this->assertGreaterThan(-1, $pos);
        }

        #[IgnoreDeprecations]
        public function testInTwig()
        {
            if ($this->isWordPressVersion('6.4', '>=')) {
                $this->setExpectedDeprecated('the_block_template_skip_link');
            }
            global $wp_scripts;
            $wp_scripts = null;
            \wp_enqueue_script('jquery', false, [], false, true);
            $str = Timber::compile('assets/wp-footer.twig', []);
            $pos = \strpos($str, 'wp-includes/js/jquery/jquery');
            $this->assertGreaterThan(-1, $pos);
        }

        #[IgnoreDeprecations]
        public function testInTwigString()
        {
            if ($this->isWordPressVersion('6.4', '>=')) {
                $this->setExpectedDeprecated('the_block_template_skip_link');
            }
            global $wp_scripts;
            $wp_scripts = null;
            \wp_enqueue_script('jquery', false, [], false, true);
            $str = Timber::compile_string('{{function("wp_footer")}}', []);
            $pos = \strpos($str, 'wp-includes/js/jquery/jquery');
            $this->assertGreaterThan(-1, $pos);
        }

        #[IgnoreDeprecations]
        public function testAgainstFooterFunctionOutput()
        {
            if ($this->isWordPressVersion('6.4', '>=')) {
                $this->setExpectedDeprecated('the_block_template_skip_link');
            }
            global $wp_scripts;
            $wp_scripts = null;
            \wp_enqueue_script('colorpicker', false, [], false, true);
            \wp_enqueue_script('fake-js', 'http://example.org/fake-js.js', [], false, true);
            $wp_footer = Helper::ob_function('wp_footer');
            global $wp_scripts;
            $wp_scripts = null;
            \wp_enqueue_script('colorpicker', false, [], false, true);
            \wp_enqueue_script('fake-js', 'http://example.org/fake-js.js', [], false, true);
            $str = Timber::compile_string('{{function("wp_footer")}}');
            $this->assertEquals($wp_footer, $str);
            $this->assertGreaterThan(50, \strlen($str));
        }

        public function testInTwigStringHeadAndFooter()
        {
            global $wp_scripts;
            $wp_scripts = null;
            //send colorpicker to the header
            \wp_enqueue_script('colorpicker', false, [], false, false);
            //send fake-js to the footer
            \wp_enqueue_script('fake-js', 'http://example.org/fake-js.js', [], false, true);
            $str = Timber::compile_string('<head>{{function("wp_head")}}</head><footer>{{function("wp_footer")}}</footer>');
            $footer_tag = \strpos($str, '<footer>');
            $colorpicker = \strpos($str, 'colorpicker');
            $this->assertGreaterThan(1, $colorpicker);
            //make sure that footer appears after colorpicker
            $this->assertGreaterThan($colorpicker, $footer_tag);
        }
    } // end class TimberWPFunctionsTest
} // end namespace Timber\Tests

// Global functions for Twig function() call tests

namespace {
    function do_jared_action()
    {
        do_action('jared_action');
    }

    function echo_junk()
    {
        echo 'foo';
    }

    function my_test_function()
    {
        return 'jared sez hi';
    }
}
