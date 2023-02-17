<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Versioned;

use Stringable;

interface SnapshotVersionComparator
{
    public function compare(int|string|Stringable $currentVersion, int|string $stateVersion): bool;
}