<?php

declare(strict_types=1);


namespace Andreo\EventSauce\Snapshotting;

interface SnapshotStateSerializer
{
    public function serialize(SnapshotState $state): array;

    public function unserialize(array $payload): SnapshotState;
}
