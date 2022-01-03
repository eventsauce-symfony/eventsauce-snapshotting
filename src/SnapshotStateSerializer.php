<?php

declare(strict_types=1);


namespace Andreo\EventSauce\Snapshotting;

interface SnapshotStateSerializer
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(SnapshotState $state): array;

    /**
     * @param array<string, mixed> $payload
     */
    public function unserialize(array $payload): SnapshotState;
}
