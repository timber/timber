<?php

namespace Timber\Twig\Extension;

use Timber\Twig\NodeVisitor\OutputBufferNodeVisitor;
use Twig\Extension\AbstractExtension;

/**
 * Keeps the output of echoing PHP functions in place when Twig renders with `use_yield`.
 *
 * @internal
 */
final class OutputBufferExtension extends AbstractExtension
{
    public function getNodeVisitors(): array
    {
        return [new OutputBufferNodeVisitor()];
    }
}
