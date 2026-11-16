<?php

namespace Timber\Twig\Node;

use Twig\Attribute\YieldReady;
use Twig\Compiler;
use Twig\Node\Node;
use Twig\Node\PrintNode;

/**
 * Runs a node inside an output buffer and yields what it echoed.
 *
 * With Twig’s `use_yield` option, a template is not rendered inside an output buffer anymore. What
 * a PHP function echoes then bypasses the template output instead of being a part of it. This node
 * puts the echoed output back where the function is called: in front of the printed value for a
 * `PrintNode`, at the position of the tag for any other node.
 *
 * @internal
 */
#[YieldReady]
final class OutputBufferNode extends Node
{
    /**
     * @param Node $node A node that does not yield, or a `PrintNode`.
     */
    public function __construct(Node $node)
    {
        parent::__construct([
            'node' => $node,
        ], [], $node->getTemplateLine());
    }

    public function compile(Compiler $compiler): void
    {
        $node = $this->getNode('node');
        $output = '$' . $compiler->getVarName();
        $value = null;

        if ($node instanceof PrintNode) {
            // The value can’t be yielded while the buffer is open, so it’s kept for later.
            $value = '$' . $compiler->getVarName();
            $compiler->addDebugInfo($node);
        }

        $compiler
            ->write("ob_start();\n")
            ->write("try {\n")
            ->indent();

        if (null !== $value) {
            $compiler
                ->write($value . ' = (string) ')
                ->subcompile($node->getNode('expr'))
                ->raw(";\n");
        } else {
            $compiler->subcompile($node);
        }

        $compiler
            ->outdent()
            ->write("} finally {\n")
            ->indent()
            ->write($output . " = ob_get_clean();\n")
            ->outdent()
            ->write("}\n")
            ->write('yield ' . $output . (null !== $value ? ' . ' . $value : '') . ";\n");
    }
}
