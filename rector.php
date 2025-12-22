<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\Array_\ArrayToFirstClassCallableRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPhpSets(
        php82: true,
    )
    ->withPreparedSets(
        // deadCode: true,
        // codeQuality: true,
        // earlyReturn: true,
    )
    ->withSkip([
        ArrayToFirstClassCallableRector::class,
        StringableForToStringRector::class,
    ]);
