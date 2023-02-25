<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Conditional\Doubles;

use EventSauce\EventSourcing\Serialization\SerializablePayload;

final class NumberFakeIncremented implements SerializablePayload
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

    public static function fromPayload(array $payload): static
    {
        return new self($payload['number']);
    }
}
