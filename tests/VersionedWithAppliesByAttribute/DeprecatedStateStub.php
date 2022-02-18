<?php

declare(strict_types=1);

namespace Tests\VersionedWithAppliesByAttribute;

final class DeprecatedStateStub
{
    public function __construct(public readonly string $value)
    {
    }
}
