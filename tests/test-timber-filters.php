<?php

class TestTimberFilters extends Timber_UnitTestCase
{
    public function testLoaderRenderDataFilter()
    {
        add_filter('timber/loader/render_data', [$this, 'filter_timber_render_data'], 10, 2);
        $output = Timber::compile('assets/output.twig', [
            'output' => 14,
        ]);
        $this->assertEquals('output.twig assets/output.twig', $output);
    }

    public function testRenderDataFilter()
    {
        add_filter('timber/render/data', function ($data, $file) {
            $data['post'] = [
                'title' => 'daaa',
            ];
            return $data;
        }, 10, 2);
        ob_start();
        Timber::render('assets/single-post.twig', [
            'fop' => 'wag',
        ]);
        $str = ob_get_clean();
        $this->assertEquals('<h1>daaa</h1>', $str);
    }

    public function filter_timber_render_data($data, $file)
    {
        $data['output'] = $file;
        return $data;
    }

    public function testOutputFilter()
    {
        add_filter('timber/output', [$this, 'filter_timber_output'], 10, 3);
        $output = Timber::compile('assets/single.twig', [
            'number' => 14,
        ]);
        $this->assertEquals('assets/single.twig14', $output);
    }

    public function filter_timber_output($output, $data, $file)
    {
        return $file . $data['number'];
    }
}
