<?php

class TestTimberFilterEscapers extends Timber_UnitTestCase
{
    public function testEscAttributeFilter()
    {
        $string = 'foo & bar';
        $native = esc_attr($string);

        $result = Timber::compile_string('{{ text|esc_attr }}', [
            'text' => $string,
        ]);

        $this->assertEquals($native, $result);
    }

    public function testEscJSFilter()
    {
        // make onlclick string
        $javascript_string = 'alert("Hello World");';
        $native = esc_js($javascript_string);

        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['autoescape'] = 'html';
            return $options;
        });

        $result = Timber::compile_string('{{ text|esc_js }}', [
            'text' => $javascript_string,
        ]);

        $this->assertEquals($native, $result);
    }

    public function testEscUrlFilter()
    {
        $dirty_url = 'https://example.com/?foo=1&bar=2';
        $other_protocol_url = 'ftp://example.com/?foo=1&bar=2';

        $native = esc_url($dirty_url);
        $other_protocol_native = esc_url($other_protocol_url);

        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['autoescape'] = 'html';
            return $options;
        });

        $result = Timber::compile_string('<a href="{{ url|esc_url }}">', [
            'url' => $dirty_url,
        ]);

        $other_protocol_result = Timber::compile_string('{{ url|esc_url }}', [
            'url' => $other_protocol_url,
        ]);

        $this->assertEquals('<a href="' . $native . '">', $result);
        $this->assertEquals($other_protocol_native, $other_protocol_result);
    }

    // Tests case as stated by https://github.com/timber/timber/issues/1848#issue-381346333
    public function testDoubleEscaper()
    {
        $string = '<p>foo & bar</p>';
        $native = wp_kses_post($string);

        $this->add_filter_temporarily('timber/twig/environment/options', function ($options) {
            $options['autoescape'] = 'html';
            return $options;
        });

        $result = Timber::compile_string('{{ text|wp_kses_post }}', [
            'text' => $string,
        ]);

        $this->assertEquals($native, $result);
    }

    public function testOldEscaper()
    {
        $dirty_url = 'https://example.com/?foo=1&bar=2';
        $other_protocol_url = 'ftp://example.com/?foo=1&bar=2';

        $native = esc_url($dirty_url);
        $other_protocol_native = esc_url($other_protocol_url);
        $result = Timber::compile_string('<a href="{{ url|e("esc_url") }}">', [
            'url' => $dirty_url,
        ]);

        $other_protocol_result = Timber::compile_string('{{ url|e("esc_url") }}', [
            'url' => $other_protocol_url,
        ]);

        $this->assertEquals('<a href="' . $native . '">', $result);
        $this->assertEquals($other_protocol_native, $other_protocol_result);
    }
}
