<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Repository\Versioned\Doubles;

use Andreo\EventSauce\Snapshotting\Aggregate\VersionedSnapshotState;

final readonly class NewSnapshotStateDummy implements VersionedSnapshotState
{
    public function __construct(public string $value = 'new')
    {
    }

    public static function getSnapshotVersion(): int|string
    {
        return 2;
    }
}
