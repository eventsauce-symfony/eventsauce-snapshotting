<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Conditional;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

final readonly class AlwaysConditionalSnapshotStrategy implements ConditionalSnapshotStrategy
{
    public function canStoreSnapshot(AggregateRootWithSnapshotting $aggregateRoot): bool
    {
        return true;
    }
}
