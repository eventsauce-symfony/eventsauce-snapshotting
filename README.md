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
use EventSauce\EventSourcing\Snapshotting\ConstructingAggregateRootRepositoryWithSnapshotting;

$snapshotRepository = new DoctrineSnapshotRepository(
    connection: $connection, // Doctrine\DBAL\Connection
    tableName: $tableName,
    serializer: $serializer, // Andreo\EventSauce\Snapshotting\SnapshotStateSerializer
    uuidEncoder: $uuidEncoder // EventSauce\UuidEncoding\UuidEncoder
)

$aggregateRepository = new ConstructingAggregateRootRepositoryWithSnapshotting(
    $aggregateRootClassName,
    $messageRepository,
    $snapshotRepository,
    $regularAggregateRootRepository
);

```

### Versioning

When your aggregate root evolves, so must your snapshots. 
A good practise is to version your snapshots.
Storing a version along with your snapshot allows you to 
filter out any outdated ones when trying to fetch 
your aggregate root

```php
use Andreo\EventSauce\Snapshotting\AggregateRootWithVersionedSnapshotting;

// Interface for aggregate root
interface AggregateRootWithVersionedSnapshotting extends AggregateRootWithSnapshotting
{
    public static function getSnapshotVersion(): int|string;
}
```

#### Usage

```php
use EventSauce\EventSourcing\AggregateRootBehaviour;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;

final class SomeAggregate implements AggregateRootWithVersionedSnapshotting
{
    use AggregateRootBehaviour;
    use VersionedSnapshottingBehaviour;

    private string $foo;
    private string $bar;

    public static function getSnapshotVersion(): int|string
    {
        return 2;
    }

    /**
     *  If you change the snapshot model, 
     *  remember to change schema version
     */
    protected function createSnapshotState(): SnapshotStateV2
    {
        return new SnapshotStateV2($this->foo, $this->bar);
    }

    protected static function reconstituteFromSnapshotState(AggregateRootId $id, $state): AggregateRootWithSnapshotting
    {
        assert($state instanceof SnapshotStateV2);

        $new = new self($id);
        
        $new->foo = $state->foo;
        $new->bar = $state->foo;

        return $new;
    }
}
```

### 