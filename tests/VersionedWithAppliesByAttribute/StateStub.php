<?php

declare(strict_types=1);

namespace Tests\VersionedWithAppliesByAttribute;

final class StateStub
{
    public function __construct(public readonly string $value)
    {
    }
}
