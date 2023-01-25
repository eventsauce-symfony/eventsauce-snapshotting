<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Aggregate;

interface VersionedSnapshotState
{
    public static function getSnapshotVersion(): int|string|object;
}
