<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Conditional;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

interface ConditionalSnapshotStrategy
{
    public function canStoreSnapshot(AggregateRootWithSnapshotting $aggregateRoot): bool;
}
