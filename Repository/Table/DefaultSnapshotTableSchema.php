<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Table;

final class DefaultSnapshotTableSchema implements SnapshotTableSchema
{
    public function incrementalIdColumn(): string
    {
        return 'id';
    }

    public function aggregateRootIdColumn(): string
    {
        return 'aggregate_root_id';
    }

    public function versionColumn(): string
    {
        return 'version';
    }

    public function payloadColumn(): string
    {
        return 'state_payload';
    }
}