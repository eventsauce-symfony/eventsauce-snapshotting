<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Serializer\Doubles;

use Andreo\EventSauce\Snapshotting\Aggregate\VersionedSnapshotState;
use EventSauce\EventSourcing\Serialization\SerializablePayload;
use Stringable;

final readonly class StateFake implements SerializablePayload, VersionedSnapshotState
{
    public function __construct(
        public string $foo = 'foo',
        public string $bar = 'bar'
    ) {
    }

    public function toPayload(): array
    {
        return [
            'foo' => $this->foo,
            'bar' => $this->bar,
        ];
    }

    public static function fromPayload(array $payload): static
    {
        return new self(
            $payload['foo'],
            $payload['bar']
        );
    }

    public static function getSnapshotVersion(): int|string|Stringable
    {
        return 1;
    }
}
