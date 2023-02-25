<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Aggregate;

use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;
use Generator;
use function assert;

/**
 * T of AggregateRootWithVersionedSnapshotting
 *
 * @template T
 */
trait SnapshottingBehaviourForDbStore
{
    use SnapshottingBehaviour;

    public function createSnapshot(): Snapshot
    {
        return new Snapshot(
            $this->aggregateRootId(),
            $this->aggregateRootVersion(),
            SnapshotState::create($this->createSnapshotState())
        );
    }

    /**
     * @return T
     */
    public static function reconstituteFromSnapshotAndEvents(Snapshot $snapshot, Generator $events): static
    {
        $id = $snapshot->aggregateRootId();
        $state = $snapshot->state();
        assert($state instanceof SnapshotState);

        /** @var T $aggregateRoot */
        $aggregateRoot = static::reconstituteFromSnapshotState($id, $state->payload);
        $aggregateRoot->aggregateRootVersion = $snapshot->aggregateRootVersion();

        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRoot->aggregateRootVersion = $events->getReturn() ?: $aggregateRoot->aggregateRootVersion;

        return $aggregateRoot;
    }
}
