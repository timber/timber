<?php

namespace Timber\Tests;

use Countable;
use Generator;
use IteratorAggregate;
use PHPUnit\Framework\Attributes\DataProvider;
use Timber\Timber;

class TimberTermTwigFiltersTest extends TimberIntegrationTestCase
{
    public function testTimberFilterSanitize()
    {
        $data['title'] = "Jared's Big Adventure";
        $str = Timber::compile_string('{{title|sanitize}}', $data);
        $this->assertEquals('jareds-big-adventure', $str);
    }

    public function testTimberPreTags()
    {
        $data = '<pre><h1>thing</h1></pre>';
        $template = '{{foo|pretags}}';
        $str = Timber::compile_string($template, [
            'foo' => $data,
        ]);
        $this->assertEquals('<pre>&lt;h1&gt;thing&lt;/h1&gt;</pre>', $str);
    }

    public function testTimberFilterString()
    {
        $data['arr'] = ['foo', 'foo'];
        $str = Timber::compile_string('{{arr|join(" ")}}', $data);
        $this->assertEquals('foo foo', \trim($str));
        $data['arr'] = ['bar'];
        $str = Timber::compile_string('{{arr|join}}', $data);
        $this->assertEquals('bar', \trim($str));
        $data['arr'] = ['foo', 'bar'];
        $str = Timber::compile_string('{{arr|join(", ")}}', $data);
        $this->assertEquals('foo, bar', \trim($str));
        $data['arr'] = 6;
        $str = Timber::compile_string('{{arr}}', $data);
        $this->assertEquals('6', \trim($str));
    }

    public function testTimberFormatBytes()
    {
        $str1 = Timber::compile_string('{{ 1200|size_format }}');
        $str2 = Timber::compile_string('{{ 1500|size_format(2) }}');
        $this->assertSame('1 KB', $str1);
        $this->assertSame('1.46 KB', $str2);
    }

    /**
     * Lists and what the `list` filter makes of them. Separators are placed by position, so the
     * keys don't matter.
     *
     * @return iterable<string, array{array, string}>
     */
    public static function listFilterProvider(): iterable
    {
        yield 'empty' => [[], ''];
        yield 'one item' => [['Tom'], 'Tom'];
        yield 'two items' => [['Tom', 'Rick'], 'Tom and Rick'];
        yield 'three items' => [['Tom', 'Rick', 'Harry'], 'Tom, Rick and Harry'];
        yield 'four items' => [['Tom', 'Rick', 'Harry', 'Mike'], 'Tom, Rick, Harry and Mike'];
        yield 'sparse integer keys' => [[
            10 => 'Tom',
            20 => 'Rick',
            30 => 'Harry',
        ], 'Tom, Rick and Harry'];
        yield 'string keys' => [[
            'first' => 'Tom',
            'second' => 'Rick',
            'third' => 'Harry',
        ], 'Tom, Rick and Harry'];
    }

    #[DataProvider('listFilterProvider')]
    public function testTwigFilterList(array $authors, string $expected)
    {
        $this->assertSame($expected, Timber::compile_string('{{authors|list}}', [
            'authors' => $authors,
        ]));
    }

    public function testTwigFilterListOxford()
    {
        $data['authors'] = ['Tom', 'Rick', 'Harry', 'Mike'];
        $str = Timber::compile_string("{{authors|list(',', ', and')}}", $data);
        $this->assertEquals('Tom, Rick, Harry, and Mike', $str);
    }

    public function testTwigFilterListCountableTraversableIteratesOnce()
    {
        $authors = new class() implements Countable, IteratorAggregate {
            public int $iterations = 0;

            public function count(): int
            {
                return 3;
            }

            public function getIterator(): Generator
            {
                ++$this->iterations;
                yield 'first' => 'Tom';
                yield 'second' => 'Rick';
                yield 'third' => 'Harry';
            }
        };
        $str = Timber::compile_string('{{authors|list}}', [
            'authors' => $authors,
        ]);
        $this->assertEquals('Tom, Rick and Harry', $str);
        $this->assertSame(1, $authors->iterations);
    }
}
