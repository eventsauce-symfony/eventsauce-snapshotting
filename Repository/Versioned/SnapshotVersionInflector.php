<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Versioned;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

interface SnapshotVersionInflector
{
    /**
     * @param class-string<AggregateRootWithSnapshotting> $aggregateRootClassName
     */
    public function snapshotVersion(string $aggregateRootClassName): int|string|object;
}