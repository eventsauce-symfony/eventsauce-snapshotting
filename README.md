## eventsauce-snapshotting 3.0

Extended snapshot components for EventSauce

[About Snapshotting](https://eventsauce.io/docs/snapshotting/)


### Installation

```bash
composer require andreo/eventsauce-snapshotting
```

#### Previous versions

- [2.0](https://github.com/eventsauce-symfony/eventsauce-snapshotting/tree/2.0.0)

### Requirements

- PHP >=8.2
- Doctrine Dbal ^3.1

### Doctrine snapshot repository

```php
use Andreo\EventSauce\Snapshotting\Doctrine\DoctrineSnapshotRepository;

new DoctrineSnapshotRepository(
    connection: $connection, // Doctrine\DBAL\Connection
    tableName: $tableName,
    serializer: $serializer, // Andreo\EventSauce\Snapshotting\Serializer\SnapshotStateSerializer
    uuidEncoder: $uuidEncoder, // EventSauce\UuidEncoding\UuidEncoder
    tableSchema: $tableSchema // Andreo\EventSauce\Snapshotting\Repository\Table\SnapshotTableSchema
)
```

### Versioning

Repository

```php
use Andreo\EventSauce\Snapshotting\Versioned\AggregateRootRepositoryWithVersionedSnapshotting;

new AggregateRootRepositoryWithVersionedSnapshotting(
    aggregateRootClassName: $aggregateRootClassName,
    messageRepository: $messageRepository
    regularRepository: $regularRepository, // EventSauce\EventSourcing\AggregateRootRepository
    snapshotVersionInflector: $snapshotVersionInflector, // Andreo\EventSauce\Snapshotting\Repository\Versioned\SnapshotVersionInflector
    snapshotVersionComparator: $snapshotVersionComparator // Andreo\EventSauce\Snapshotting\Repository\Versioned\SnapshotVersionComparator
);
```

Versioned Snapshot State

```php

use Andreo\EventSauce\Snapshotting\Versioned\VersionedSnapshotState;

final class FooSnapshotStateV2 implements VersionedSnapshotState {

    public static function getSnapshotVersion(): int|string|Stringable
    {
        return 2;
    }
}

```
Example of aggregate

```php

use Andreo\EventSauce\Snapshotting\Aggregate\VersionedSnapshottingBehaviour;
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

final class FooAggregate implements AggregateRootWithSnapshotting
{
    use AggregateRootBehaviour;
    use VersionedSnapshottingBehaviour;

    // Create snapshot method must have type hint of VersionedSnapshotState implementation (with default SnapshotVersionInflector)
    protected function createSnapshotState(): FooSnapshotStateV2
    {
        return new FooSnapshotStateV2();
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof FooSnapshotStateV2);
    }
}
```

### Conditional Strategy

```php


interface ConditionalSnapshotStrategy
{
    public function canStoreSnapshot(AggregateRootWithSnapshotting $aggregateRoot): bool;
}
```

#### Built-in strategies

Every n event

```php

use Andreo\EventSauce\Snapshotting\Conditional\AggregateRootRepositoryWithConditionalSnapshot;use Andreo\EventSauce\Snapshotting\Conditional\EveryNEventConditionalSnapshotStrategy;

new AggregateRootRepositoryWithConditionalSnapshot(
    regularRepository: $regularRepository, // EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting
    conditionalSnapshotStrategy: new EveryNEventConditionalSnapshotStrategy(numberOfEvents: 200) # or your implementation
);
```
