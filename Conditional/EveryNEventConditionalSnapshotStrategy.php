<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Conditional;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

final readonly class EveryNEventConditionalSnapshotStrategy implements ConditionalSnapshotStrategy
{
    public function __construct(private int $numberOfEvents = 100)
    {
    }

    public function canStoreSnapshot(AggregateRootWithSnapshotting $aggregateRoot): bool
    {
        return 0 === $aggregateRoot->aggregateRootVersion() % $this->numberOfEvents;
    }
}
