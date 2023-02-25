<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use RuntimeException;

final class UnableInflectSnapshotVersion extends RuntimeException
{
    public static function createSnapshotStateMethodDoesNotExists(string $aggregateRootClassName, string $createSnapshotStateMethod): self
    {
        return new self(
            sprintf('Method %s does not exists in %s.', $createSnapshotStateMethod, $aggregateRootClassName)
        );
    }
    public static function invalidReturnedTypeOfSnapshotCreationMethod(string $createSnapshotStateMethod): self
    {
        return new self(
            sprintf('Method %s should return type of VersionedSnapshotState implementation.', $createSnapshotStateMethod)
        );
    }
}