<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use Stringable;

interface VersionedSnapshotState
{
    public static function getSnapshotVersion(): int|string|Stringable;
}
