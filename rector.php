<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    )
    ->withImportNames(importNames: false, importDocBlockNames: false, importShortClasses: false, removeUnusedImports: true)
    ->withSkip([
        Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector::class,
    ]);
