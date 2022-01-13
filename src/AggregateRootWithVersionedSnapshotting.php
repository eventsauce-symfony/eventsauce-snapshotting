<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

interface AggregateRootWithVersionedSnapshotting extends AggregateRootWithSnapshotting
{
    public static function getSnapshotVersion(): int|string;
}
