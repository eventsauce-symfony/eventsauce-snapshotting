<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Versioned\Doubles;

use Andreo\EventSauce\Snapshotting\Versioned\VersionedSnapshotState;
use Stringable;

final readonly class NewSnapshotStateDummy implements VersionedSnapshotState
{
    public function __construct(public string $value = 'new')
    {
    }

    public static function getSnapshotVersion(): int|string|Stringable
    {
        return 2;
    }
}
