<?php

declare(strict_types=1);

namespace Tests\EveryNEvent;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class AggregateNumberIncremented implements SerializablePayload
{
    private int $number;

    public function __construct(int $number)
    {
        $this->number = $number;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function toPayload(): array
    {
        return [
            'number' => $this->number,
        ];
    }

    public static function fromPayload(array $payload): self
    {
        return new self($payload['number']);
    }
}
