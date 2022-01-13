<?php

declare(strict_types=1);

namespace Tests\VersionedSnapshotting;

final class StateStub
{
    public function __construct(public readonly string $value)
    {
    }
}
