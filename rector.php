<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\StringableForToStringRector;
use Rector\Php81\Rector\Array_\FirstClassCallableRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(
        php81: true,
    )
    ->withPreparedSets(
        // deadCode: true,
        // codeQuality: true,
        // earlyReturn: true,
    )
    ->withSkip([
        FirstClassCallableRector::class,
        StringableForToStringRector::class,
    ]);
