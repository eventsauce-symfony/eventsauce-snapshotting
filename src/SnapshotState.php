<?php

declare(strict_types=1);


namespace Andreo\EventSauce\Snapshotting;

use DateTimeImmutable;
use function assert, is_int;

final class SnapshotState
{
    private const CREATED_AT_FORMAT = 'Y-m-d H:i:s.uO';

    /**
     * @param array<string, int|string|array<mixed>|bool|float|null> $headers
     */
    private function __construct(
        public readonly object $state,
        public array $headers = []
    ){}

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

    public function schemaVersion(): int
    {
        $version = $this->headers[Header::SCHEMA_VERSION->value];
        assert(is_int($version));

        return $version;
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

    /**
     * @param array<string, int|string|array<mixed>|bool|float|null> $headers
     */
    public static function from(object $state, array $headers = []): self
    {
        return new self($state, $headers);
    }
}