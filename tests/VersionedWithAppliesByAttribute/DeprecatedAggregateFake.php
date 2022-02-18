<?php

declare(strict_types=1);

namespace Tests\VersionedWithAppliesByAttribute;

use Andreo\EventSauce\Snapshotting\AggregateRootWithVersionedSnapshotting;
use Andreo\EventSauce\Snapshotting\VersionedSnapshottingBehaviour;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

class DeprecatedAggregateFake implements AggregateRootWithVersionedSnapshotting
{
    use AggregateRootBehaviour;
    use VersionedSnapshottingBehaviour;

    public string $value = 'deprecated';

    public static function create(AggregateRootId $id): self
    {
        return new self($id);
    }

    protected function createSnapshotState(): DeprecatedStateStub
    {
        return new DeprecatedStateStub($this->value);
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof DeprecatedStateStub);

        $new = new self($id);
        $new->value = $state->value;

        return $new;
    }

    public static function getSnapshotVersion(): int|string
    {
        return 1;
    }
}
