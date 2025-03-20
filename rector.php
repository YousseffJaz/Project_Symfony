<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Doctrine\Set\DoctrineSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src'
    ]);

    // Symfony 6 avec PHP 8.0
    $rectorConfig->sets([
        SetList::PHP_80,
        SymfonySetList::SYMFONY_60,
        DoctrineSetList::DOCTRINE_ORM_29,
        LevelSetList::UP_TO_PHP_80
    ]);

    // Règles spécifiques pour PHP 8.0
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(false);

    // Ignorer certains dossiers
    $rectorConfig->skip([
        __DIR__ . '/var',
        __DIR__ . '/vendor',
    ]);
}; 