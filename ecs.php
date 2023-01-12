<?php

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

    // Imports
    $ecsConfig->rule(FullyQualifiedStrictTypesFixer::class);
    $ecsConfig->rule(NoLeadingImportSlashFixer::class);
    $ecsConfig->rule(SingleImportPerStatementFixer::class);
    $ecsConfig->ruleWithConfiguration(GlobalNamespaceImportFixer::class, [
        'import_classes' => true,
    ]);
};
