<?php

declare(strict_types=1);


namespace Andreo\EventSauce\Snapshotting;

enum Header: string
{
    case CREATED_AT = '__created_at';
    case STATE_TYPE = '__state_type';
    case SCHEMA_VERSION = '__schema_version';
}