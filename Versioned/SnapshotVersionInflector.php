<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use Stringable;

interface SnapshotVersionInflector
{
    /**
     * @param class-string<AggregateRootWithSnapshotting> $aggregateRootClassName
     */
    public function snapshotVersion(string $aggregateRootClassName): int|string|Stringable;
}