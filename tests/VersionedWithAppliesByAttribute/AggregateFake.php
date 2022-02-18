<?php

declare(strict_types=1);

namespace Tests\VersionedWithAppliesByAttribute;

use Andreo\EventSauce\Aggregate\AggregateRootBehaviourWithAppliesByAttribute;
use Andreo\EventSauce\Snapshotting\AggregateRootWithVersionedSnapshotting;
use Andreo\EventSauce\Snapshotting\VersionedSnapshottingBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

class AggregateFake implements AggregateRootWithVersionedSnapshotting
{
    use AggregateRootBehaviourWithAppliesByAttribute;
    use VersionedSnapshottingBehaviour;

    public string $value = 'new';

    public static function create(AggregateRootId $id): self
    {
        return new self($id);
    }

    protected function createSnapshotState(): StateStub
    {
        return new StateStub($this->value);
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof StateStub);

        $new = new self($id);
        $new->value = $state->value;

        return $new;
    }

    public static function getSnapshotVersion(): int|string
    {
        return 2;
    }
}
