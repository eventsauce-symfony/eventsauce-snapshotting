<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Versioned\Doubles;

use Andreo\EventSauce\Snapshotting\Aggregate\SnapshottingBehaviourForDbStore;
use Andreo\EventSauce\Snapshotting\Aggregate\VersionedSnapshottingBehaviour;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

class DeprecatedAggregateFake implements AggregateRootWithSnapshotting
{
    use AggregateRootBehaviour;
    use VersionedSnapshottingBehaviour;

    public string $value;

    public static function create(AggregateRootId $id): self
    {
        return new self($id);
    }

    protected function createSnapshotState(): DeprecatedSnapshotStateDummy
    {
        return new DeprecatedSnapshotStateDummy();
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof DeprecatedSnapshotStateDummy);

        $new = new self($id);
        $new->value = $state->value;

        return $new;
    }
}
