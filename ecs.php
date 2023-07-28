<?php

use PhpCsFixer\Fixer\FunctionNotation\NativeFunctionInvocationFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\SingleImportPerStatementFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests', 'ecs.php']);

    $ecsConfig->skip([
        NotOperatorWithSuccessorSpaceFixer::class,
    ]);

    $ecsConfig->sets([SetList::PSR_12, SetList::ARRAY, SetList::SPACES, SetList::NAMESPACES]);

    /**
     * Import
     * @see https://cs.symfony.com/doc/rules/index.html#import
     */
    $ecsConfig->rule(FullyQualifiedStrictTypesFixer::class);
    $ecsConfig->rule(NoLeadingImportSlashFixer::class);
    $ecsConfig->rule(SingleImportPerStatementFixer::class);
    $ecsConfig->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
    ]);

    /**
     * NativeFunctionInvocation
     * @see https://cs.symfony.com/doc/rules/function_notation/native_function_invocation.html
     */
    $ecsConfig->ruleWithConfiguration(NativeFunctionInvocationFixer::class, [
        'include' => [
            '@all',
        ],
        'scope' => 'namespaced',
        'strict' => true,
    ]);
};
