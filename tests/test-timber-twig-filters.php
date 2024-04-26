<?php

class TestTimberTermTwigFilters extends Timber_UnitTestCase
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
        $this->assertEquals('foo foo', trim($str));
        $data['arr'] = ['bar'];
        $str = Timber::compile_string('{{arr|join}}', $data);
        $this->assertEquals('bar', trim($str));
        $data['arr'] = ['foo', 'bar'];
        $str = Timber::compile_string('{{arr|join(", ")}}', $data);
        $this->assertEquals('foo, bar', trim($str));
        $data['arr'] = 6;
        $str = Timber::compile_string('{{arr}}', $data);
        $this->assertEquals('6', trim($str));
    }

    public function testTimberFormatBytes()
    {
        $str1 = Timber::compile_string('{{ 1200|size_format }}');
        $str2 = Timber::compile_string('{{ 1500|size_format(2) }}');
        $this->assertSame('1 KB', $str1);
        $this->assertSame('1.46 KB', $str2);
    }

    public function testTwigFilterList()
    {
        $data['authors'] = ['Tom', 'Rick', 'Harry', 'Mike'];
        $str = Timber::compile_string("{{authors|list}}", $data);
        $this->assertEquals('Tom, Rick, Harry and Mike', $str);
    }

    public function testTwigFilterListOxford()
    {
        $data['authors'] = ['Tom', 'Rick', 'Harry', 'Mike'];
        $str = Timber::compile_string("{{authors|list(',', ', and')}}", $data);
        $this->assertEquals('Tom, Rick, Harry, and Mike', $str);
    }
}
