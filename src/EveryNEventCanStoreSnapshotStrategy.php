<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

final class EveryNEventCanStoreSnapshotStrategy implements CanStoreSnapshotStrategy
{
    public function __construct(private int $numberOfEvents)
    {
    }

    public function canStore(AggregateRootWithSnapshotting $aggregateRoot): bool
    {
        return 0 === $aggregateRoot->aggregateRootVersion() % $this->numberOfEvents;
    }
}
