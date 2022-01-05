<?php

declare(strict_types=1);

namespace Tests\DbalRepository;

use Andreo\EventSauce\Doctrine\Migration\SnapshotSchemaBuilder;
use Andreo\EventSauce\Snapshotting\ConstructingSnapshotStateSerializer;
use Andreo\EventSauce\Snapshotting\DoctrineDbalSnapshotRepository;
use Andreo\EventSauce\Snapshotting\SnapshotState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Types;
use EventSauce\Clock\SystemClock;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\Serialization\ConstructingPayloadSerializer;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\UuidEncoding\BinaryUuidEncoder;
use PHPUnit\Framework\TestCase;

final class DoctrineDbalSnapshotRepositoryTest extends TestCase
{
    private const TABLE_NAME = 'snapshot';

    private AggregateRootId $aggregateRootId;

    private Connection $connection;

    /**
     * @test
     */
    public function repository_persist_and_retrieve_last_snapshot(): void
    {
        $repository = $this->repository();
        foreach ($this->snapshots() as $snapshot) {
            $repository->persist($snapshot);
        }

        $snapshot = $repository->retrieve($this->aggregateRootId);
        $this->assertEquals(3, $snapshot->aggregateRootVersion());
    }

    public function snapshots(): array
    {
        return [
                new Snapshot(
                    $this->aggregateRootId,
                    1,
                    SnapshotState::from(new StateStub('foo'))
                ),
                new Snapshot(
                    $this->aggregateRootId,
                    2,
                    SnapshotState::from(new StateStub('bar'))
                ),
                new Snapshot(
                    $this->aggregateRootId,
                    3,
                    SnapshotState::from(new StateStub('baz'))
                ),
        ];
    }

    protected function setUp(): void
    {
        $snapshotSchemaBuilder = new SnapshotSchemaBuilder();
        $this->aggregateRootId = DummyAggregateId::create();
        $this->connection = DriverManager::getConnection(
            [
                'dbname' => 'eventsauce_snapshot',
                'user' => 'username',
                'password' => 'pswd',
                'host' => 'mysql',
                'port' => 3306,
                'driver' => 'pdo_mysql',
            ]
        );

        $schemaManager = $this->connection->createSchemaManager();
        if ($schemaManager->tablesExist(self::TABLE_NAME)) {
            $this->connection->executeQuery('TRUNCATE TABLE `' . self::TABLE_NAME . '`');
        } else {
            $snapshotSchema = $snapshotSchemaBuilder->build(self::TABLE_NAME, Types::BINARY);
            $sql = $snapshotSchema->toSql($this->connection->getDatabasePlatform());

            $this->connection->executeQuery($sql[0]);
        }
    }

    private function repository(): DoctrineDbalSnapshotRepository
    {
        return new DoctrineDbalSnapshotRepository(
            $this->connection,
            self::TABLE_NAME,
            new ConstructingSnapshotStateSerializer(
                new ConstructingPayloadSerializer(),
                new SystemClock(),
                new DotSeparatedSnakeCaseInflector(),
            ),
            new BinaryUuidEncoder()
        );
    }
}