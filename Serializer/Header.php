<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Serializer;

enum Header: string
{
    case CREATED_AT = '__created_at';
    case STATE_TYPE = '__state_type';
    case VERSION = '__snapshot_version';
}
