<?php

namespace Timber\Tests;

use PHPUnit\Framework\Attributes\Group;
use Timber\Loader;
use Timber\Timber;

/**
 * Test Timber's render_twig_block and compile_twig_block functionality
 */
#[Group('timber-render-block')]
class TimberRenderBlockTest extends TimberIntegrationTestCase
{
    /**
     * Test compile_twig_block with valid block name
     */
    public function testCompileTwigBlockValidBlock()
    {
        $data = [
            'message' => 'Test error message',
        ];
        $output = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', $data);

        $expected = '<div class="top-0 left-0 w-full p-4 font-bold bg-red-500 text-red-50" role="alert">Test error message</div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test compile_twig_block with nested block
     */
    public function testCompileTwigBlockNestedBlock()
    {
        $data = [
            'content' => 'nested content',
        ];
        $output = Timber::compile_twig_block('nested_inner', 'Fixtures/assets/toasts.twig', $data);

        $expected = '<p>This is a nested block: nested content</p>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test compile_twig_block with invalid block name - should render entire template
     */
    public function testCompileTwigBlockInvalidBlock()
    {
        $data = [
            'message' => 'Test message',
        ];
        $output = Timber::compile_twig_block('nonexistent', 'Fixtures/assets/toasts.twig', $data);

        // When block doesn't exist, should render the whole template
        $this->assertStringContainsString('This is the default template content', $output);
        $this->assertStringContainsString('bg-red-500', $output); // Contains error block
        $this->assertStringContainsString('bg-green-500', $output); // Contains success block
    }

    /**
     * Test compile_twig_block with template that has no blocks
     */
    public function testCompileTwigBlockNoBlocksInTemplate()
    {
        $output = Timber::compile_twig_block('nonexistent', 'Fixtures/assets/no-blocks.twig');

        // Should render the entire template when block doesn't exist
        $expected = '<div class="empty-template">This template has no blocks</div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test compile_twig_block with empty data
     */
    public function testCompileTwigBlockEmptyData()
    {
        $output = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', []);

        // Should work with empty message
        $expected = '<div class="top-0 left-0 w-full p-4 font-bold bg-red-500 text-red-50" role="alert"></div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test compile_twig_block with no data parameter
     */
    public function testCompileTwigBlockNoData()
    {
        $output = Timber::compile_twig_block('info', 'Fixtures/assets/toasts.twig');

        // Should work without data parameter
        $expected = '<div class="top-0 left-0 w-full p-4 bg-blue-500 text-blue-50" role="alert"></div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test render_twig_block with valid block name - captures output
     */
    public function testRenderTwigBlockValidBlock()
    {
        $data = [
            'message' => 'Test render message',
        ];

        // Capture output from render_twig_block
        \ob_start();
        Timber::render_twig_block('success', 'Fixtures/assets/toasts.twig', $data);
        $output = \ob_get_contents();
        \ob_end_clean();

        $expected = '<div class="top-0 left-0 w-full p-4 bg-green-500 text-green-50" role="alert">Test render message</div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test render_twig_block with invalid block name - should render entire template
     */
    public function testRenderTwigBlockInvalidBlock()
    {
        $data = [
            'message' => 'Test message',
        ];

        // Capture output from render_twig_block
        \ob_start();
        Timber::render_twig_block('nonexistent', 'Fixtures/assets/toasts.twig', $data);
        $output = \ob_get_contents();
        \ob_end_clean();

        // When block doesn't exist, should render the whole template
        $this->assertStringContainsString('This is the default template content', $output);
        $this->assertStringContainsString('bg-red-500', $output); // Contains error block
    }

    /**
     * Test render_twig_block with missing template file
     */
    public function testRenderTwigBlockMissingTemplate()
    {
        // Capture output and error suppression
        \ob_start();
        Timber::render_twig_block('error', 'Fixtures/assets/nonexistent.twig', [
            'message' => 'test',
        ]);
        $output = \ob_get_contents();
        \ob_end_clean();

        // Should output nothing when template doesn't exist
        $this->assertEmpty(\trim($output));
    }

    /**
     * Test compile_twig_block with caching
     */
    public function testCompileTwigBlockWithCache()
    {
        $data = [
            'message' => 'Cached message',
        ];

        // First call - should cache
        $output1 = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', $data, expires: 60);

        // Second call - should use cache
        $output2 = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', $data, expires: 60);

        $expected = '<div class="top-0 left-0 w-full p-4 font-bold bg-red-500 text-red-50" role="alert">Cached message</div>';
        $this->assertEquals($expected, \trim($output1));
        $this->assertEquals($expected, \trim($output2));
        $this->assertEquals($output1, $output2);
    }

    /**
     * Test that compile_twig_block passes through the correct parameters to compile
     */
    public function testCompileTwigBlockParameterPassing()
    {
        // Test all parameters are passed correctly
        $block_name = 'info';
        $template = 'Fixtures/assets/toasts.twig';
        $data = [
            'message' => 'Parameter test',
        ];
        $expires = 30;
        $cache_mode = Loader::CACHE_TRANSIENT;

        $output = Timber::compile_twig_block($block_name, $template, $data, expires: $expires, cache_mode: $cache_mode);

        $expected = '<div class="top-0 left-0 w-full p-4 bg-blue-500 text-blue-50" role="alert">Parameter test</div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test that block names are case sensitive
     */
    public function testBlockNameCaseSensitive()
    {
        $data = [
            'message' => 'Case test',
        ];

        // Correct case should work
        $output_correct = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', $data);
        $this->assertStringContainsString('bg-red-500', $output_correct);

        // Wrong case should render entire template (block not found)
        $output_wrong = Timber::compile_twig_block('ERROR', 'Fixtures/assets/toasts.twig', $data);
        $this->assertStringContainsString('This is the default template content', $output_wrong);
    }

    /**
     * Test that render_twig_block and compile_twig_block produce the same output
     */
    public function testRenderTwigBlockAndCompileConsistency()
    {
        $data = [
            'message' => 'Consistency test',
        ];

        // Get output from compile_twig_block
        $compiled_output = Timber::compile_twig_block('warning', 'Fixtures/assets/toasts.twig', $data);

        // Get output from render_twig_block
        \ob_start();
        Timber::render_twig_block('warning', 'Fixtures/assets/toasts.twig', $data);
        $rendered_output = \ob_get_contents();
        \ob_end_clean();

        // Both should produce identical output
        $this->assertEquals(\trim($compiled_output), \trim($rendered_output));
    }

    /**
     * Test error handling when template file doesn't exist
     */
    public function testCompileTwigBlockMissingTemplate()
    {
        $output = Timber::compile_twig_block('error', 'Fixtures/assets/nonexistent.twig', [
            'message' => 'test',
        ]);

        // Should return false when template doesn't exist
        $this->assertFalse($output);
    }

    /**
     * Test empty block name handling
     */
    public function testCompileTwigBlockEmptyBlockName()
    {
        $data = [
            'message' => 'Test message',
        ];
        $output = Timber::compile_twig_block('', 'Fixtures/assets/toasts.twig', $data);

        // Should render entire template when block name is empty
        $this->assertStringContainsString('This is the default template content', $output);
    }

    /**
     * Test block inheritance behavior
     */
    public function testCompileTwigBlockInheritance()
    {
        // Test that nested blocks work correctly
        $data = [
            'content' => 'inheritance test',
        ];
        $output = Timber::compile_twig_block('nested', 'Fixtures/assets/toasts.twig', $data);

        // Should render the parent block content including nested inner block
        $this->assertStringContainsString('This is a nested block: inheritance test', $output);
    }

    /**
     * Test render_twig_block Twig function usage within templates
     */
    public function testRenderTwigBlockTwigFunction()
    {
        $data = [
            'toast_type' => 'success',
            'toast_message' => 'Function test message',
        ];
        $php_unit = $this;
        \add_filter('timber/locations', function ($paths) use ($php_unit) {
            $paths[] = [__DIR__];
            return $paths;
        });
        // First, let's test if the function exists and can be called manually
        $manual_output = Timber::compile_twig_block('success', 'Fixtures/assets/toasts.twig', $data);
        $this->assertStringContainsString('bg-green-500', $manual_output);

        // Now test if the Twig function works
        $output = Timber::compile('Fixtures/assets/test-twig-function.twig', $data);

        // Should contain the rendered block content
        $this->assertStringContainsString('bg-green-500', $output); // Success toast styling
        $this->assertStringContainsString('Function test message', $output);
        $this->assertStringContainsString('Main Content', $output); // Main template content
        $this->assertStringContainsString('<html lang="en">', $output); // Full HTML structure
    }

    /**
     * Test render_twig_block Twig function with invalid block name
     */
    public function testRenderTwigBlockTwigFunctionInvalidBlock()
    {
        $php_unit = $this;
        \add_filter('timber/locations', function ($paths) use ($php_unit) {
            $paths[] = [__DIR__];
            return $paths;
        });
        $data = [
            'toast_type' => 'nonexistent',
            'toast_message' => 'This should fallback to full template',
        ];

        $output = Timber::compile('Fixtures/assets/test-twig-function.twig', $data);

        // Should contain fallback content when block doesn't exist
        $this->assertStringContainsString('This is the default template content', $output);
        $this->assertStringContainsString('Main Content', $output); // Main template still renders
    }

    /**
     * Test rendering different blocks from a layout template
     */
    public function testCompileTwigBlockFromLayout()
    {
        // Test title block
        $title_output = Timber::compile_twig_block('title', 'Fixtures/assets/base-layout.twig', [
            'page_title' => 'Custom Title',
        ]);
        $this->assertEquals('Default Title', \trim($title_output));

        // Test header block
        $header_output = Timber::compile_twig_block('header', 'Fixtures/assets/base-layout.twig');
        $this->assertEquals('<h1>Default Header</h1>', \trim($header_output));

        // Test content block with data
        $content_output = Timber::compile_twig_block('content', 'Fixtures/assets/base-layout.twig', [
            'custom_content' => 'Test content',
        ]);
        $this->assertEquals('<p>Default content</p>', \trim($content_output));
    }

    /**
     * Test compile_twig_block with explicitly null data parameter
     * This covers the is_null($data) check in Timber.php line 1562
     */
    public function testCompileTwigBlockWithNullData()
    {
        $output = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', null);

        // Should work with null data parameter (converts to empty array)
        $expected = '<div class="top-0 left-0 w-full p-4 font-bold bg-red-500 text-red-50" role="alert"></div>';
        $this->assertEquals($expected, \trim($output));
    }

    /**
     * Test compile_twig_block with array of non-existent templates
     * This covers the implode line in Timber.php line 1567
     */
    public function testCompileTwigBlockMissingTemplateArray()
    {
        $filenames = ['Fixtures/assets/nonexistent1.twig', 'Fixtures/assets/nonexistent2.twig', 'Fixtures/assets/nonexistent3.twig'];
        $output = Timber::compile_twig_block('error', $filenames, [
            'message' => 'test',
        ]);

        // Should return false when none of the templates exist
        $this->assertFalse($output);
    }

    /**
     * Test compile_twig_block with caching when data contains recursive reference
     * This tests the caching behavior when json_encode would fail
     * Covers Loader.php line 166 and TwigBlockLoader.php line 38
     */
    public function testCompileTwigBlockWithRecursiveData()
    {
        // Create a stdClass object with a recursive reference to trigger json_encode failure
        $obj = new \stdClass();
        $obj->self = $obj; // Create circular reference

        $data = [
            'message' => 'Test message',
            'recursive' => $obj,
        ];

        // When json_encode fails, caching should be skipped but rendering should still work
        $output = Timber::compile_twig_block('error', 'Fixtures/assets/toasts.twig', $data, expires: 60);

        // Should still render despite caching issue
        $expected = '<div class="top-0 left-0 w-full p-4 font-bold bg-red-500 text-red-50" role="alert">Test message</div>';
        $this->assertEquals($expected, \trim($output));
    }
}
