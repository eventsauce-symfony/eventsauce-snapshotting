<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Aggregate;

use LogicException;

final class SnapshotIsNotVersioned extends LogicException
{
    public static function fromPayload(object $payload): self
    {
        return new self(
            sprintf("State of type %s is not versioned. Add VersionedSnapshotState implementation.", $payload::class)
        );
    }
}