<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

/**
 * @implements AggregateRootRepositoryWithSnapshotting<AggregateRootWithSnapshotting>
 */
final class AggregateRootRepositoryWithSnapshottingAndStoreStrategy implements AggregateRootRepositoryWithSnapshotting
{
    /**
     * @param AggregateRootRepositoryWithSnapshotting<AggregateRootWithSnapshotting> $regularRepository
     */
    public function __construct(
        private AggregateRootRepositoryWithSnapshotting $regularRepository,
        private CanStoreSnapshotStrategy $canStoreSnapshotStrategy
    ) {}

    public function retrieveFromSnapshot(AggregateRootId $aggregateRootId): object
    {
        return $this->regularRepository->retrieveFromSnapshot($aggregateRootId);
    }

    public function storeSnapshot(AggregateRootWithSnapshotting $aggregateRoot): void
    {
        if ($this->canStoreSnapshotStrategy->canStore($aggregateRoot)) {
            $this->regularRepository->storeSnapshot($aggregateRoot);
        }
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->regularRepository->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot): void
    {
        $this->regularRepository->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
    {
        $this->regularRepository->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }
}
