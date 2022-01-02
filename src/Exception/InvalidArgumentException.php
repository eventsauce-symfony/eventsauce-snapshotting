<?php

declare(strict_types=1);


namespace Andreo\EventSauce\Snapshotting\Exception;

use Andreo\EventSauce\Snapshotting\AggregateRootWithVersionedSnapshotting;
use EventSauce\EventSourcing\EventSauceException;
use InvalidArgumentException as BaseException;

final class InvalidArgumentException extends BaseException implements EventSauceException
{
    public static function aggregateMustBeVersioned(): self
    {
        return new self(sprintf(
            'Aggregate root repository with versionable snapshotting require that aggregate be an instance of %s',
                AggregateRootWithVersionedSnapshotting::class,
            )
        );
    }
}