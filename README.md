## eventsauce-snapshotting

Extended snapshot components of the EventSauce

[About Snapshotting](https://eventsauce.io/docs/snapshotting/)


### Installation

```bash
composer require andreo/eventsauce-snapshotting
```
### Requirements

- PHP ^8.1
- Doctrine Dbal ^3.1

### Doctrine snapshot repository

By default, EventSauce only provides a memory repository 
for storing snapshots.
This library provides a repository to be stored in the 
database by doctrine.

#### Usage

```php
use Andreo\EventSauce\Snapshotting\DoctrineSnapshotRepository;

new DoctrineSnapshotRepository(
    connection: $connection, // Doctrine\DBAL\Connection
    tableName: $tableName,
    serializer: $serializer, // Andreo\EventSauce\Snapshotting\SnapshotStateSerializer
    uuidEncoder: $uuidEncoder // EventSauce\UuidEncoding\UuidEncoder
)
```

### Versioning

When your aggregate root evolves, so must your snapshots. 
A good practise is to version your snapshots.
Storing a version along with your snapshot allows you to 
filter out any outdated ones when trying to fetch 
your aggregate root

```php
use Andreo\EventSauce\Snapshotting\AggregateRootWithVersionedSnapshotting;

interface AggregateRootWithVersionedSnapshotting extends AggregateRootWithSnapshotting
{
    public static function getSnapshotVersion(): int|string;
}
```

#### Usage

Aggregate
```php
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

final class SomeAggregate implements AggregateRootWithVersionedSnapshotting
{
    use AggregateRootBehaviour;
    use VersionedSnapshottingBehaviour; // dedicated trait

    private string $foo;
    private string $bar;

    /**
     *  If you change the snapshot model, 
     *  remember to change schema version
     */
    protected function createSnapshotState(): SnapshotStateV2
    {
        return new SnapshotStateV2($this->foo, $this->bar);
    }

    public static function getSnapshotVersion(): int|string
    {
        return 2;
    }
    
    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof SnapshotStateV2);
        
        // do something
    }
}
```
Repository

```php
use Andreo\EventSauce\Snapshotting\AggregateRootRepositoryWithVersionedSnapshotting;

new AggregateRootRepositoryWithVersionedSnapshotting(
    aggregateRootClassName: $aggregateRootClassName,
    messageRepository: $messageRepository
    snapshotRepository: $snapshotRepository // EventSauce\EventSourcing\AggregateRootRepository
);
```

### Store strategy

```php
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

interface CanStoreSnapshotStrategy
{
    public function canStore(AggregateRootWithSnapshotting $aggregateRoot): bool;
}
```

#### Every n event

```php
use Andreo\EventSauce\Snapshotting\AggregateRootRepositoryWithSnapshottingAndStoreStrategy;
use Andreo\EventSauce\Snapshotting\EveryNEventCanStoreSnapshotStrategy;

new AggregateRootRepositoryWithSnapshottingAndStoreStrategy(
    regularRepository: $regularRepository, // EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting
    canStoreSnapshotStrategy: new EveryNEventCanStoreSnapshotStrategy(numberOfEvents: 200) // or other implementation of Andreo\EventSauce\Snapshotting\CanStoreSnapshotStrategy
);
```