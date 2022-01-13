<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

interface CanStoreSnapshotStrategy
{
    public function canStore(AggregateRootWithSnapshotting $aggregateRoot): bool;
}
