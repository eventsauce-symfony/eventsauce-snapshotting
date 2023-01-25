<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Repository\Versioned\Doubles;

use Andreo\EventSauce\Snapshotting\Aggregate\VersionedSnapshottingBehaviour;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

class NewAggregateFake implements AggregateRootWithSnapshotting
{
    use AggregateRootBehaviour;
    use VersionedSnapshottingBehaviour;

    public string $value = 'new';

    protected function createSnapshotState(): NewSnapshotStateDummy
    {
        return new NewSnapshotStateDummy();
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof NewSnapshotStateDummy);

        $new = new self($id);
        $new->value = $state->value;

        return $new;
    }
}
