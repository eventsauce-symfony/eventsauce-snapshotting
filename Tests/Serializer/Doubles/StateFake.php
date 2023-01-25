<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Serializer\Doubles;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final readonly class StateFake implements SerializablePayload
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
}
