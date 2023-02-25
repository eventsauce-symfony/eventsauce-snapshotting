<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Doctrine;

use EventSauce\EventSourcing\EventSauceException;
use RuntimeException;
use Throwable;

final class UnableToRetrieveSnapshot extends RuntimeException implements EventSauceException
{
    public static function dueTo(string $reason = '', Throwable $previous = null): self
    {
        return new self("Unable to retrieve messages. {$reason}", 0, $previous);
    }
}
