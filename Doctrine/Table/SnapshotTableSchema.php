<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Doctrine\Table;

interface SnapshotTableSchema
{
    public function incrementalIdColumn(): string;

    public function aggregateRootIdColumn(): string;

    public function versionColumn(): string;

    public function payloadColumn(): string;
}