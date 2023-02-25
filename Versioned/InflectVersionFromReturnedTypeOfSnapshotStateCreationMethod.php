<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use Stringable;

final readonly class InflectVersionFromReturnedTypeOfSnapshotStateCreationMethod implements SnapshotVersionInflector
{
    public function __construct(private string $createSnapshotStateMethod = 'createSnapshotState')
    {
    }

    /**
     * @param class-string<AggregateRootWithSnapshotting> $aggregateRootClassName
     */
    public function snapshotVersion(string $aggregateRootClassName): int|string|Stringable
    {
        try {
            $createSnapshotStateReflection = new ReflectionMethod($aggregateRootClassName, $this->createSnapshotStateMethod);
        } catch (ReflectionException) {
            throw UnableInflectSnapshotVersion::createSnapshotStateMethodDoesNotExists($aggregateRootClassName, $this->createSnapshotStateMethod);
        }

        $snapshotStateReturnType = $createSnapshotStateReflection->getReturnType();
        /** @var ReflectionType[] $snapshotStateReflectionTypes */
        $snapshotStateReflectionTypes = [];
        if ($snapshotStateReturnType instanceof ReflectionNamedType) {
            $snapshotStateReflectionTypes = [$snapshotStateReturnType];
        } elseif ($snapshotStateReturnType instanceof ReflectionIntersectionType) {
            $snapshotStateReflectionTypes = $snapshotStateReturnType->getTypes();
        }
        foreach ($snapshotStateReflectionTypes as $snapshotStateReflectionType) {
            if (!$snapshotStateReflectionType instanceof ReflectionNamedType) {
                continue;
            }
            /** @var class-string<VersionedSnapshotState>|class-string $snapshotStateTypeName */
            $snapshotStateTypeName = $snapshotStateReflectionType->getName();
            if (is_subclass_of($snapshotStateTypeName, VersionedSnapshotState::class)) {
                return $snapshotStateTypeName::getSnapshotVersion();
            }
        }

        throw UnableInflectSnapshotVersion::invalidReturnedTypeOfSnapshotCreationMethod($this->createSnapshotStateMethod);
    }
}