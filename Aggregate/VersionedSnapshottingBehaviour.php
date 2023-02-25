<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Aggregate;

use Andreo\EventSauce\Snapshotting\Versioned\VersionedSnapshotState;

/**
 * T of AggregateRootWithVersionedSnapshotting
 *
 * @template T
 */
trait VersionedSnapshottingBehaviour
{
    use SnapshottingBehaviourForDbStore;

    abstract protected function createSnapshotState(): VersionedSnapshotState;
}
