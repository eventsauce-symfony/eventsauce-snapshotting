<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use LogicException;

final class SnapshotIsNotVersioned extends LogicException
{
    public static function fromPayload(mixed $payload): self
    {
        return new self(
            sprintf("State of type %s is not versioned. Add VersionedSnapshotState implementation.", get_debug_type($payload))
        );
    }
}