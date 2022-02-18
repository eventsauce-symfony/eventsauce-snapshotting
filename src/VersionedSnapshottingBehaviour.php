<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;
use Generator;
use function assert;

/**
 * T of AggregateRootWithVersionedSnapshotting.
 *
 * @template T
 */
trait VersionedSnapshottingBehaviour
{
    use SnapshottingBehaviour;

    public function createSnapshot(): Snapshot
    {
        return new Snapshot(
            $this->aggregateRootId(),
            $this->aggregateRootVersion(),
            SnapshotState::from(
                $this->createSnapshotState(),
                [Header::SCHEMA_VERSION->value => static::getSnapshotVersion()]
            )
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
        $aggregateRoot = static::reconstituteFromSnapshotState($id, $state->state);
        $aggregateRoot->aggregateRootVersion = $snapshot->aggregateRootVersion();

        foreach ($events as $event) {
            $aggregateRoot->apply($event);
        }

        $aggregateRoot->aggregateRootVersion = $events->getReturn() ?: $aggregateRoot->aggregateRootVersion;

        return $aggregateRoot;
    }
}
