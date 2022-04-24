<?php

declare(strict_types=1);

namespace Tests\DoctrineRepository;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class StateStub implements SerializablePayload
{
    public function __construct(public readonly string $value)
    {
    }

    public function toPayload(): array
    {
        return ['value' => $this->value];
    }

    public static function fromPayload(array $payload): static
    {
        return new self($payload['value']);
    }
}
