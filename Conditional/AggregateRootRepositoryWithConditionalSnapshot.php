<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Conditional;

use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

/**
 * @template T of AggregateRootWithSnapshotting
 *
 * @implements AggregateRootRepositoryWithSnapshotting<T>
 */
final readonly class AggregateRootRepositoryWithConditionalSnapshot implements AggregateRootRepositoryWithSnapshotting
{
    /**
     * @param AggregateRootRepositoryWithSnapshotting<T> $regularRepository
     */
    public function __construct(
        private AggregateRootRepositoryWithSnapshotting $regularRepository,
        private ConditionalSnapshotStrategy $conditionalSnapshotStrategy
    ) {
    }

    public function retrieveFromSnapshot(AggregateRootId $aggregateRootId): object
    {
        return $this->regularRepository->retrieveFromSnapshot($aggregateRootId);
    }

    /**
     * @param T $aggregateRoot
     */
    public function storeSnapshot(AggregateRootWithSnapshotting $aggregateRoot): void
    {
        if ($this->conditionalSnapshotStrategy->canStoreSnapshot($aggregateRoot)) {
            $this->regularRepository->storeSnapshot($aggregateRoot);
        }
    }

    /**
     * @return T
     */
    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->regularRepository->retrieve($aggregateRootId);
    }

    /**
     * @param T $aggregateRoot
     */
    public function persist(object $aggregateRoot): void
    {
        $this->regularRepository->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
    {
        $this->regularRepository->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }
}
