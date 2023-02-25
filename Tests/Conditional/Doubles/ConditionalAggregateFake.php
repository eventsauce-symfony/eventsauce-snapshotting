<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Conditional\Doubles;

use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\SnapshottingBehaviour;

class ConditionalAggregateFake implements AggregateRootWithSnapshotting
{
    use AggregateRootBehaviour;
    use SnapshottingBehaviour;

    private int $incrementedNumber = 0;

    public function increment(): void
    {
        $this->recordThat(
            new NumberFakeIncremented(
                $this->incrementedNumber + 1
            )
        );
    }

    protected function applyNumberFakeIncremented(NumberFakeIncremented $event): void
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
