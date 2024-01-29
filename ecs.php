<?php

use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests', 'ecs.php'])
    ->withSkip([
        NotOperatorWithSuccessorSpaceFixer::class,
    ])
    /**
     * Import
     * @see https://cs.symfony.com/doc/rules/index.html#import
     */
    ->withRules([
        FullyQualifiedStrictTypesFixer::class,
        NoLeadingImportSlashFixer::class,
        SingleImportPerStatementFixer::class,
    ])
    ->withConfiguredRule(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
    ])
    /**
     * NativeFunctionInvocation
     * @see https://cs.symfony.com/doc/rules/function_notation/native_function_invocation.html
     */
    ->withConfiguredRule(NativeFunctionInvocationFixer::class, [
        'include' => [
            '@all',
        ],
        'scope' => 'namespaced',
        'strict' => true,
    ])
    ->withSets([
        SetList::PSR_12,
        SetList::ARRAY,
        SetList::SPACES,
        SetList::NAMESPACES,
    ])
;
