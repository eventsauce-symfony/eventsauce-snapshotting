<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Tests\Repository\Doctrine;

use Andreo\EventSauce\Aggregate\Tests\DummyAggregateId;
use Andreo\EventSauce\Snapshotting\Aggregate\SnapshotState;
use Andreo\EventSauce\Snapshotting\Repository\Doctrine\DoctrineSnapshotRepository;
use Andreo\EventSauce\Snapshotting\Serializer\SnapshotStateSerializer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\UuidEncoding\BinaryUuidEncoder;
use PHPUnit\Framework\TestCase;
use stdClass;

final class DoctrineSnapshotRepositoryTest extends TestCase
{
    private const UUID = 'f7d175c9-fbf6-4662-be41-014fff236e79';

    /**
     * @test
     *
     * @dataProvider getSnapshots
     */
    public function should_retrieve_snapshot_with_version(Snapshot $snapshot): void
    {
        $repository = $this->buildRepository($snapshot->aggregateRootVersion());

        $repository->persist($snapshot);

        $snapshot = $repository->retrieve(DummyAggregateId::fromString(self::UUID));
        $this->assertEquals($snapshot->aggregateRootVersion(), $snapshot->aggregateRootVersion());
    }

    public function getSnapshots(): array
    {
        $id = DummyAggregateId::fromString(self::UUID);
        return [
            [
                new Snapshot(
                    $id,
                    1,
                    SnapshotState::create(new stdClass())
                ),
            ],
            [
                new Snapshot(
                    $id,
                    3,
                    SnapshotState::create(new stdClass())
                ),
            ],
            [
                new Snapshot(
                    $id,
                    2,
                    SnapshotState::create(new stdClass())
                ),
            ],
            [
                new Snapshot(
                    $id,
                    4,
                    SnapshotState::create(new stdClass())
                ),
            ]
        ];
    }

    private function buildRepository(int $aggregateVersion): DoctrineSnapshotRepository
    {
        $queryBuilderMock = $this->createConfiguredMock(QueryBuilder::class, [
            'select' => $this->createConfiguredMock(QueryBuilder::class, [
                'from' => $this->createConfiguredMock(QueryBuilder::class, [
                    'where' => $this->createConfiguredMock(QueryBuilder::class, [
                        'orderBy' => $this->createConfiguredMock(QueryBuilder::class, [
                            'setMaxResults' => $this->createMock(QueryBuilder::class)
                        ])
                    ])
                ])
            ]),
            'executeQuery' => $this->createConfiguredMock(Result::class, [
                'fetchAssociative' => [
                    'state' => '{}',
                    'aggregate_root_version' => $aggregateVersion
                ]
            ])
        ]);

        $connection = $this->createConfiguredMock(Connection::class, [
            'createQueryBuilder' => $queryBuilderMock,
        ]);

        $serializerMock = $this->createConfiguredMock(SnapshotStateSerializer::class, [
            'serialize' => [],
            'unserialize' => SnapshotState::create(new stdClass())
        ]);

        return new DoctrineSnapshotRepository(
            $connection,
            'foo',
            $serializerMock,
            new BinaryUuidEncoder()
        );
    }
}
