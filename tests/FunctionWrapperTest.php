<?php

namespace Timber\Tests {
    use Timber\FunctionWrapper;
    use Timber\Timber;
    use Twig\TwigFunction;

    class FunctionWrapperTest extends TimberIntegrationTestCase
    {
        public function testToStringWithException()
        {
            \ob_start();
            $wrapper = new FunctionWrapper('Timber\Tests\FunctionWrapperTest::isNum', ['hi']);
            echo $wrapper;
            $content = \trim(\ob_get_contents());
            \ob_end_clean();
            $this->assertEquals('Caught exception: Argument must be of type integer', $content);
        }

        public function testToStringWithoutException()
        {
            \ob_start();
            $wrapper = new FunctionWrapper('Timber\Tests\FunctionWrapperTest::isNum', [4]);
            echo $wrapper;
            $content = \trim(\ob_get_contents());
            \ob_end_clean();
            $this->assertSame('1', $content);
        }

        public function testToStringWithClassObject()
        {
            \ob_start();
            $wrapper = new FunctionWrapper($this->isNum(...), [4]);
            echo $wrapper;
            $content = \trim(\ob_get_contents());
            \ob_end_clean();
            $this->assertSame('1', $content);
        }

        public function testToStringWithClassString()
        {
            \ob_start();
            $wrapper = new FunctionWrapper([static::class, 'isNum'], [4]);
            echo $wrapper;
            $content = \trim(\ob_get_contents());
            \ob_end_clean();
            $this->assertSame('1', $content);
        }

        public function testWPHead()
        {
            $context = Timber::context();
            $title = \uniqid('Test Blog', true);
            $this->add_filter_temporarily('wp_head', function () use ($title) {
                echo '<title>' . $title . '</title>';
            });
            $str = Timber::compile_string("{{ function('wp_head') }}", $context);
            $this->assertStringContainsString('<title>' . $title . '</title>', \trim($str));
        }

        public function testFunctionInTemplate()
        {
            $context = Timber::context();
            $str = Timber::compile_string("{{ function('my_boo') }}", $context);
            $this->assertEquals('bar!', \trim($str));
        }

        public function testNakedSoloFunction()
        {
            \add_filter('timber/twig', function ($twig) {
                $twig->addFunction(new TwigFunction('your_boo', $this->your_boo(...)));
                return $twig;
            });
            $context = Timber::context();
            $str = Timber::compile_string("{{ your_boo() }}", $context);
            $this->assertEquals('yourboo', \trim($str));
        }

        /* Sample function to test exception handling */

        public static function isNum($num)
        {
            if (!\is_int($num)) {
                throw new \Exception("Argument must be of type integer");
            } else {
                return true;
            }
        }

        public function your_boo()
        {
            return 'yourboo';
        }
    }
}

// Global function for Twig function() call test

namespace {
    function my_boo()
    {
        return 'bar!';
    }
}
