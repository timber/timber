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
        if ($node instanceof PrintNode) {
            $expr = $node->getNode('expr');

            if ($expr instanceof ConstantExpression) {
                return $node;
            }

            // A generator is yielded from, so there is no value to keep. This is the case for
            // `{{ parent() }}` and `{{ block('name') }}`, where the block has its own nodes.
            if ($expr instanceof AbstractExpression && $expr->isGenerator()) {
                return $node;
            }

            return new OutputBufferNode($node);
        }

        if ($node instanceof DoNode) {
            return new OutputBufferNode($node);
        }

        // With `{% set name %}…{% endset %}`, the body has its own nodes.
        if ($node instanceof SetNode && !$node->getAttribute('capture') && !$node->getNode('values') instanceof ConstantExpression) {
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
