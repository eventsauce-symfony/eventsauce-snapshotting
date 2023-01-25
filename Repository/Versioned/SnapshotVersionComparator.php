<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Versioned;

interface SnapshotVersionComparator
{
    public function compare(int|string|object $versionA, int|string|object $versionB): bool;
}