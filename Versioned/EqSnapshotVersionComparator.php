<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use Stringable;

final class EqSnapshotVersionComparator implements SnapshotVersionComparator
{
    public function compare(int|string|Stringable $currentVersion, int|string $stateVersion): bool
    {
        return $currentVersion === $stateVersion;
    }
}