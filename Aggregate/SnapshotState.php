<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Aggregate;

use Andreo\EventSauce\Snapshotting\Serializer\Header;
use DateTimeImmutable;

final class SnapshotState
{
    private const CREATED_AT_FORMAT = 'Y-m-d H:i:s.uO';

    /**
     * @param object|VersionedSnapshotState $payload
     * @param array<string, int|string|array<mixed>|bool|float> $headers
     */
    private function __construct(
        public readonly object $payload,
        public array           $headers = []
    ) {
    }

    /**
     * @param array<string, int|string|array<mixed>|bool|float> $headers
     */
    public static function create(object $state, array $headers = []): self
    {
        return new self($state, $headers);
    }

    public function withCreatedAt(DateTimeImmutable $createdAt): self
    {
        return $this->withHeader(Header::CREATED_AT, $createdAt->format(self::CREATED_AT_FORMAT));
    }

    public function withStateType(string $type): self
    {
        return $this->withHeader(Header::STATE_TYPE, $type);
    }

    /**
     * @param int|string|array<mixed>|bool|float $value
     */
    public function withHeader(Header $key, int|string|array|bool|float $value): self
    {
        $clone = clone $this;
        $clone->headers[$key->value] = $value;

        return $clone;
    }

    public function version(): int|string|object
    {
        if ($this->payload instanceof VersionedSnapshotState) {
            return $this->payload::getSnapshotVersion();
        }

        throw SnapshotIsNotVersioned::fromPayload($this->payload);
    }

    public function exists(string $header): bool
    {
        return null !== $this->header($header);
    }

    /**
     * @return int|string|array<mixed>|bool|float|null
     */
    public function header(string $key): int|string|array|bool|float|null
    {
        return $this->headers[$key] ?? null;
    }
}
