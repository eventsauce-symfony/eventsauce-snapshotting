<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Versioned;

final class EqSnapshotVersionComparator implements SnapshotVersionComparator
{
    public function compare(object|int|string $versionA, object|int|string $versionB): bool
    {
        return $versionA === $versionB;
    }
}