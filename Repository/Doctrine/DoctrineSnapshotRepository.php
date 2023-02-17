<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Repository\Doctrine;

use Andreo\EventSauce\Snapshotting\Aggregate\SnapshotState;
use Andreo\EventSauce\Snapshotting\Repository\Table\DefaultSnapshotTableSchema;
use Andreo\EventSauce\Snapshotting\Repository\Table\SnapshotTableSchema;
use Andreo\EventSauce\Snapshotting\Serializer\SnapshotStateSerializer;
use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use EventSauce\UuidEncoding\UuidEncoder;
use Throwable;

final class DoctrineSnapshotRepository implements SnapshotRepository
{
    /**
     * @param int<1, max> $jsonDepth
     * @param array<int> $jsonEncodeFlags
     * @param array<int> $jsonDecodeFlags
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly string $tableName,
        private readonly SnapshotStateSerializer $serializer,
        private readonly UuidEncoder $uuidEncoder,
        private readonly SnapshotTableSchema $tableSchema = new DefaultSnapshotTableSchema(),
        private readonly int $jsonDepth = 512,
        private array $jsonEncodeFlags = [],
        private array $jsonDecodeFlags = []
    ) {
        $this->jsonEncodeFlags[] = JSON_THROW_ON_ERROR;
        $this->jsonDecodeFlags[] = JSON_THROW_ON_ERROR;
    }

    /**
     * @param Snapshot<object|SnapshotState> $snapshot
     */
    public function persist(Snapshot $snapshot): void
    {
        $state = $snapshot->state();
        if (!$state instanceof SnapshotState) {
            $state = SnapshotState::create($state);
        }

        $payload = $this->serializer->serialize($state);

        try {
            $rootId = $this->uuidEncoder->encodeString($snapshot->aggregateRootId()->toString());
            $jsonEncodeFlags = $this->computeJsonFlags($this->jsonEncodeFlags);
            $statePayload = json_encode($payload, $jsonEncodeFlags, $this->jsonDepth);
            $this->connection->insert(
                $this->tableName,
                [
                    $this->tableSchema->aggregateRootIdColumn() => $rootId,
                    $this->tableSchema->versionColumn() => $snapshot->aggregateRootVersion(),
                    $this->tableSchema->payloadColumn() => $statePayload,
                ]
            );
        } catch (Throwable $exception) {
            throw UnableToPersistSnapshot::dueTo(previous: $exception);
        }
    }

    /**
     * @return Snapshot<SnapshotState>|null
     */
    public function retrieve(AggregateRootId $id): ?Snapshot
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select(
                sprintf('%s AS version', $this->tableSchema->versionColumn()),
                sprintf('%s AS payload', $this->tableSchema->payloadColumn())
            )
            ->from($this->tableName)
            ->where(sprintf('%s = :aggregate_root_id', $this->tableSchema->aggregateRootIdColumn()))
            ->orderBy($this->tableSchema->versionColumn(), 'DESC')
            ->setMaxResults(1)
            ->setParameter('aggregate_root_id', $this->uuidEncoder->encodeString($id->toString()))
        ;

        try {
            if (false === $result = $builder->executeQuery()->fetchAssociative()) {
                return null;
            }

            /** @var int<1, max> $aggregateRootVersion */
            $aggregateRootVersion = $result['version'];
            /** @var string $jsonPayload */
            $jsonPayload = $result['payload'];

            $jsonDecodeFlags = $this->computeJsonFlags($this->jsonDecodeFlags);
            /** @var array<string, array<mixed>> $statePayload */
            $statePayload = json_decode(
                $jsonPayload,
                true,
                $this->jsonDepth,
                $jsonDecodeFlags
            );

            $state = $this->serializer->unserialize($statePayload);
        } catch (Throwable $exception) {
            throw UnableToRetrieveSnapshot::dueTo(previous: $exception);
        }

        return new Snapshot(
            $id,
            $aggregateRootVersion,
            $state
        );
    }

    /**
     * @param int[] $flags
     */
    private function computeJsonFlags(array $flags): int
    {
        return array_reduce($flags, static fn (int $a, int $b) => $a | $b, 0);
    }
}
