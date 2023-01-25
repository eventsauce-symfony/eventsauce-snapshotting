<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Repository\Versioned\Doubles;

use Andreo\EventSauce\Snapshotting\Aggregate\VersionedSnapshotState;

final readonly class DeprecatedSnapshotStateDummy implements VersionedSnapshotState
{
    public function __construct(public string $value = 'deprecated')
    {
    }

    public static function getSnapshotVersion(): int|string
    {
        return 1;
    }
}
