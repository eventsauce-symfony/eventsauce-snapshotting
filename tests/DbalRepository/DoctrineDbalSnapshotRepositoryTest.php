<?php

declare(strict_types=1);

namespace Tests\DbalRepository;

use Andreo\EventSauce\Snapshotting\ConstructingSnapshotStateSerializer;
use Andreo\EventSauce\Snapshotting\DoctrineDbalSnapshotRepository;
use Andreo\EventSauce\Snapshotting\SnapshotState;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
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
    private string $tableName = 'snapshot';

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
        parent::setUp();
        $this->aggregateRootId = DummyAggregateId::create();
        $this->connection = DriverManager::getConnection(
            [
                'dbname' => 'test_snapshot',
                'user' => 'username',
                'password' => 'pswd',
                'host' => '127.0.0.1',
                'port' => 3306,
                'driver' => 'pdo_mysql',
            ]
        );

        $schemaManager = $this->connection->createSchemaManager();
        if ($schemaManager->tablesExist($this->tableName)) {
            $this->connection->executeQuery('TRUNCATE TABLE `' . $this->tableName . '`');
        } else {
            $sql = $this->createSnapshotTableQuery($this->connection);
            $this->connection->executeQuery($sql[0]);
        }
    }

    private function createSnapshotTableQuery(Connection $connection): array
    {
        $tableSchema = new Schema();
        $table = $tableSchema->createTable($this->tableName);
        $table->addColumn('id', Types::INTEGER, [
            'length' => 20,
            'unsigned' => true,
            'autoincrement' => true,
        ]);
        $table->addColumn('aggregate_root_id', Types::BINARY, [
            'length' => 16,
            'fixed' => true,
        ]);
        $table->addColumn('aggregate_root_version', Types::INTEGER, [
            'length' => 20,
            'unsigned' => true,
        ]);
        $table->addColumn('state', Types::STRING, [
            'length' => 16001,
        ]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(
            ['aggregate_root_id', 'aggregate_root_version'],
            'last'
        );
        $table->addOption('charset', 'utf8mb4');
        $table->addOption('collation', 'utf8mb4_general_ci');

        return $tableSchema->toSql($connection->getDatabasePlatform());
    }

    private function repository(): DoctrineDbalSnapshotRepository
    {
        return new DoctrineDbalSnapshotRepository(
            $this->connection,
            $this->tableName,
            new ConstructingSnapshotStateSerializer(
                new ConstructingPayloadSerializer(),
                new SystemClock(),
                new DotSeparatedSnakeCaseInflector(),
            ),
            new BinaryUuidEncoder()
        );
    }
}