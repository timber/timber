<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Timber\FunctionWrapper;
use Timber\Loader;
use Timber\Timber;
use Timber\Twig\Extension\OutputBufferExtension;
use Twig\Environment;
use Twig\Error\RuntimeError;

/**
 * Twig’s `use_yield` option must not change where the output of an echoing function ends up.
 */
class TwigOutputBufferTest extends TimberIntegrationTestCase
{
    /**
     * The output buffer level around the template that flushes its buffers.
     */
    private int $level = 0;

    public function set_up()
    {
        parent::set_up();

        $this->add_action_temporarily('timber_test_echo', function ($text) {
            echo $text;
        });

        $this->add_filter_temporarily('timber/twig/functions', function ($functions) {
            $functions['echo_and_return'] = [
                'callable' => function ($text, $value = null) {
                    echo $text;

                    return $value;
                },
            ];
            $functions['render_string'] = [
                'callable' => [Timber::class, 'render_string'],
            ];
            $functions['flush_buffers'] = [
                'callable' => function () {
                    // Flushes the buffer that the template is rendered in, which is below the one
                    // of the tag with `use_yield`.
                    $nested = \ob_get_level() - $this->level - 1;

                    for ($i = 0; $i < $nested; $i++) {
                        \ob_end_flush();
                    }

                    \ob_flush();

                    for ($i = 0; $i < $nested; $i++) {
                        \ob_start();
                    }
                },
            ];
            $functions['throw_after_echo'] = [
                'callable' => function ($text): never {
                    echo $text;

                    throw new RuntimeException('thrown');
                },
            ];

            return $functions;
        });

        $this->add_filter_temporarily('timber/twig/filters', function ($filters) {
            $filters['echo_and_return'] = [
                'callable' => function ($value, $text) {
                    echo $text;

                    return $value;
                },
            ];

            return $filters;
        });
    }

    public static function templateProvider(): iterable
    {
        $templates = [
            'fn' => [
                '[{{ fn("_e", "echoed") }}]',
                '[echoed]',
            ],
            'function' => [
                '[{{ function("_e", "echoed") }}]',
                '[echoed]',
            ],
            'function filter' => [
                '[{{ "_e"|function("echoed") }}]',
                '[echoed]',
            ],
            'action' => [
                '[{{ action("timber_test_echo", "echoed") }}]',
                '[echoed]',
            ],
            'do action' => [
                '[{% do action("timber_test_echo", "echoed") %}]',
                '[echoed]',
            ],
            'do fn' => [
                '[{% do fn("_e", "echoed") %}]',
                '[echoed]',
            ],
            'return value is printed after the echoed output' => [
                '[{{ echo_and_return("echoed", "returned") }}{{ echo_and_return("echoed", true) }}]',
                '[echoedreturnedechoed1]',
            ],
            'set' => [
                '[{% set value = echo_and_return("echoed", "returned") %}]<{{ value }}>',
                '[echoed]<returned>',
            ],
            'set with several names' => [
                '[{% set first, second = echo_and_return("one", 1), echo_and_return("two", 2) %}]<{{ first }}{{ second }}>',
                '[onetwo]<12>',
            ],
            'if' => [
                '[{% if echo_and_return("echoed", true) %}yes{% endif %}]',
                '[echoedyes]',
            ],
            'Timber functions' => [
                '{% set captured %}[{{ _e("echoed") }}|{{ bloginfo("name") }}]{% endset %}<{{ captured }}>',
                '<[echoed|Test Blog]>',
            ],
            'custom function and filter' => [
                '{% set captured %}[{{ echo_and_return("echoed", "returned") }}|{{ "value"|echo_and_return("echoed") }}]{% endset %}<{{ captured }}>',
                '<[echoedreturned|echoedvalue]>',
            ],
            'object' => [
                '{% set captured %}[{{ wrapper }}|{{ wrapper.call("called") }}]{% endset %}<{{ captured }}>',
                '<[echoed|called]>',
            ],
            'function that renders a template' => [
                '{% set captured %}[{{ render_string("{{ fn(\'_e\', \'echoed\') }}") }}]{% endset %}<{{ captured }}>',
                '<[echoed]>',
            ],
            'set block' => [
                '{% set captured %}[{{ fn("_e", "printed") }}|{% do action("timber_test_echo", "done") %}|{% set value = echo_and_return("set") %}]{% endset %}<{{ captured }}>',
                '<[printed|done|set]>',
            ],
            'apply' => [
                '{% apply upper %}[{{ fn("_e", "printed") }}|{% do action("timber_test_echo", "done") %}]{% endapply %}',
                '[PRINTED|DONE]',
            ],
            'macro' => [
                '{% macro echo() %}[{{ fn("_e", "printed") }}|{% do action("timber_test_echo", "done") %}]{% endmacro %}<{{ _self.echo() }}>',
                '<[printed|done]>',
            ],
            'include function' => [
                '<{{ include("assets/block-echo.twig") }}>',
                '<[echoed]>',
            ],
            'include tag' => [
                '<{% include "assets/block-echo.twig" %}>',
                '<[echoed]>',
            ],
            'block function' => [
                '{% block echo %}[{{ fn("_e", "echoed") }}]{% endblock %}<{{ block("echo") }}>',
                '[echoed]<[echoed]>',
            ],
            'parent function' => [
                '{% extends "assets/block-echo.twig" %}{% block echo %}<{{ parent() }}>{% endblock %}',
                '<[echoed]>',
            ],
        ];

        foreach ($templates as $name => [$template, $expected]) {
            yield $name => [$template, $expected, false];
            yield $name . ' with use_yield' => [$template, $expected, true];
        }
    }

    #[DataProvider('templateProvider')]
    public function testEchoedOutputStaysInPlace(string $template, string $expected, bool $use_yield)
    {
        $this->use_yield($use_yield);

        \ob_start();
        $output = Timber::compile_string($template, [
            'wrapper' => new FunctionWrapper('_e', ['echoed']),
        ]);
        $leaked = \ob_get_clean();

        $this->assertSame($expected, $output);
        $this->assertSame('', $leaked);
    }

    /**
     * Only `{{ }}`, `{% do %}` and `{% set %}` have their own output buffer, so an echo in another
     * tag relies on the buffer that the template is rendered in.
     */
    #[DataProvider('useYieldProvider')]
    public function testCompileKeepsEchoedOutputInPlace(bool $use_yield)
    {
        $this->use_yield($use_yield);

        \ob_start();
        $output = Timber::compile('assets/block-echo-condition.twig');
        $leaked = \ob_get_clean();

        $this->assertSame('[echoedyes]', \trim($output));
        $this->assertSame('', $leaked);
    }

    #[DataProvider('useYieldProvider')]
    public function testCompileTwigBlockKeepsEchoedOutputInPlace(bool $use_yield)
    {
        $this->use_yield($use_yield);

        \ob_start();
        $output = Timber::compile_twig_block('echo', 'assets/block-echo-condition.twig');
        $leaked = \ob_get_clean();

        $this->assertSame('[echoedyes]', \trim($output));
        $this->assertSame('', $leaked);
    }

    /**
     * Without debug, Twig discards what is flushed out of the buffer of a template, for example
     * when a function exits.
     */
    #[DataProvider('flushProvider')]
    public function testFlushedOutputIsDiscardedWithoutDebug(bool $use_yield, bool $debug, string $expected)
    {
        $this->use_yield($use_yield);
        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) use ($debug) {
            $options['debug'] = $debug;

            return $options;
        });

        \ob_start();
        $this->level = \ob_get_level();
        $output = Timber::compile_string('flushed{% do flush_buffers() %}kept');
        $leaked = \ob_get_clean();

        $this->assertSame('kept', $output);
        $this->assertSame($expected, $leaked);
    }

    public static function flushProvider(): iterable
    {
        yield 'without use_yield' => [false, false, ''];
        yield 'with use_yield' => [true, false, ''];
        yield 'without use_yield, with debug' => [false, true, 'flushed'];
        yield 'with use_yield, with debug' => [true, true, 'flushed'];
    }

    #[DataProvider('useYieldProvider')]
    public function testEchoedOutputIsNotEscaped(bool $use_yield)
    {
        $this->use_yield($use_yield);
        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['autoescape'] = 'html';

            return $options;
        });

        $output = Timber::compile_string('{{ echo_and_return("<b>echoed</b>", "<i>returned</i>") }}');

        $this->assertSame('<b>echoed</b>&lt;i&gt;returned&lt;/i&gt;', $output);
    }

    #[DataProvider('useYieldProvider')]
    public function testEchoedOutputIsDiscardedOnException(bool $use_yield)
    {
        $this->use_yield($use_yield);
        $level = \ob_get_level();

        \ob_start();
        try {
            Timber::compile_string("before\n\n{{ throw_after_echo('echoed') }}");
            $this->fail('The exception was not thrown.');
        } catch (RuntimeError $e) {
            $this->assertSame(3, $e->getTemplateLine());
            $this->assertInstanceOf(RuntimeException::class, $e->getPrevious());
        } finally {
            $leaked = \ob_get_clean();
        }

        $this->assertSame('', $leaked);
        $this->assertSame($level, \ob_get_level());
    }

    #[DataProvider('useYieldProvider')]
    public function testExtensionIsOnlyAddedWithUseYield(bool $use_yield)
    {
        $this->use_yield($use_yield);

        $twig = (new Loader())->get_twig();

        // Twig 4 always renders with generators.
        $expected = $use_yield || Environment::MAJOR_VERSION >= 4;

        $this->assertSame($expected, $twig->hasExtension(OutputBufferExtension::class));
    }

    public static function useYieldProvider(): iterable
    {
        yield 'without use_yield' => [false];
        yield 'with use_yield' => [true];
    }

    private function use_yield(bool $use_yield): void
    {
        // Runs after the filter of the test bootstrap, which enables the option for a whole run.
        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) use ($use_yield) {
            $options['use_yield'] = $use_yield;

            return $options;
        }, 20);
    }
}
