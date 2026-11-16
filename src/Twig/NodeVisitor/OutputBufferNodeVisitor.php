<?php

namespace Timber\Twig\NodeVisitor;

use Timber\Twig\Node\OutputBufferNode;
use Twig\Environment;
use Twig\Node\DoNode;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Node\PrintNode;
use Twig\Node\SetNode;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Wraps the nodes that can run an echoing PHP function in an output buffer.
 *
 * These are `{{ … }}`, `{% do … %}` and `{% set name = … %}`.
 *
 * @internal
 */
final class OutputBufferNodeVisitor implements NodeVisitorInterface
{
    public function enterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        // A subclass can compile to anything, so only Twig’s own nodes are wrapped.
        switch ($node::class) {
            case PrintNode::class:
                $expr = $node->getNode('expr');

                if ($expr instanceof ConstantExpression) {
                    return $node;
                }

                // A generator is yielded from, so there is no value to keep.
                if ($expr instanceof AbstractExpression && $expr->isGenerator()) {
                    return $node;
                }

                return new OutputBufferNode($node);

            case DoNode::class:
                return new OutputBufferNode($node);

            case SetNode::class:
                // With `{% set name %}…{% endset %}`, the body has its own nodes.
                if ($node->getAttribute('capture') || $node->getNode('values') instanceof ConstantExpression) {
                    return $node;
                }

                return new OutputBufferNode($node);
        }

        return $node;
    }

    /**
     * Runs after Twig’s optimizer (255), which replaces some `PrintNode` instances.
     */
    public function getPriority(): int
    {
        return 256;
    }
}
