<?php

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

	$ecsConfig->skip([
		NotOperatorWithSuccessorSpaceFixer::class,
	]);

	$ecsConfig->sets([SetList::PSR_12, SetList::ARRAY, SetList::SPACES, SetList::NAMESPACES]);
};
