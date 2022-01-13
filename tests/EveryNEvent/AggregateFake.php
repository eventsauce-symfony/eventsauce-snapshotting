<?php

declare(strict_types=1);

namespace Tests\EveryNEvent;

use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;

class AggregateFake implements AggregateRootWithSnapshotting
{
    use AggregateRootBehaviour;
    use SnapshottingBehaviour;

    private int $incrementedNumber = 0;

    public function increment(): void
    {
        $this->recordThat(
            new AggregateNumberIncremented(
                $this->incrementedNumber + 1
            )
        );
    }

    protected function applyAggregateNumberIncremented(AggregateNumberIncremented $event): void
    {
        $this->incrementedNumber = $event->getNumber();
    }

    protected function createSnapshotState(): int
    {
        return $this->incrementedNumber;
    }

    public function getIncrementedNumber(): int
    {
        return $this->incrementedNumber;
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        $new = new self($id);
        $new->incrementedNumber = $state;

        return $new;
    }
}
