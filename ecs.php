<?php

declare(strict_types=1);

// ecs.php
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalInternalClassFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->parallel();

    $ecsConfig->paths([
        __DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/ecs.php'
    ]);

    $ecsConfig->skip([
        __DIR__ . '/tests/_support/_generated'
    ]);

    $ecsConfig->import(SetList::PSR_12);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short'
    ]);
    $ecsConfig->rule(NoUnusedImportsFixer::class);
    $ecsConfig->ruleWithConfiguration(FinalInternalClassFixer::class, [
        'annotation_exclude' => ['@not-fix'],
        'annotation_include' => [],
        'consider_absent_docblock_as_internal_class' => \true
    ]);
};
