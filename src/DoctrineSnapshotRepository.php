<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use Andreo\EventSauce\Snapshotting\Exception\UnableToPersistSnapshot;
use Andreo\EventSauce\Snapshotting\Exception\UnableToRetrieveSnapshot;
use Doctrine\DBAL\Connection;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use EventSauce\UuidEncoding\UuidEncoder;
use Throwable;

final class DoctrineSnapshotRepository implements SnapshotRepository
{
    public function __construct(
        private Connection $connection,
        private string $tableName,
        private SnapshotStateSerializer $serializer,
        private UuidEncoder $uuidEncoder
    ) {
    }

    public function persist(Snapshot $snapshot): void
    {
        /** @var object $state */
        $state = $snapshot->state();
        if (!$state instanceof SnapshotState) {
            $state = SnapshotState::from($state);
        }

        $payload = $this->serializer->serialize($state);

        try {
            $this->connection->insert(
                $this->tableName,
                [
                    'aggregate_root_id' => $this->uuidEncoder->encodeString($snapshot->aggregateRootId()->toString()),
                    'aggregate_root_version' => $snapshot->aggregateRootVersion(),
                    'state' => json_encode($payload, JSON_THROW_ON_ERROR),
                ]
            );
        } catch (Throwable $exception) {
            throw UnableToPersistSnapshot::dueTo(previous: $exception);
        }
    }

    public function retrieve(AggregateRootId $id): ?Snapshot
    {
        $builder = $this->connection->createQueryBuilder();
        $builder
            ->select('aggregate_root_version', 'state')
            ->from($this->tableName)
            ->where('aggregate_root_id = :aggregate_root_id')
            ->orderBy('aggregate_root_version', 'DESC')
            ->setMaxResults(1)
            ->setParameter('aggregate_root_id', $this->uuidEncoder->encodeString($id->toString()))
        ;

        try {
            if (false === $result = $builder->executeQuery()->fetchAssociative()) {
                return null;
            }

            /** @var int $aggregateRootVersion */
            $aggregateRootVersion = $result['aggregate_root_version'];
            /** @var string $state */
            $state = $result['state'];

            /** @var array<string, array<mixed>> $payload */
            $payload = json_decode($state, true, 512, JSON_THROW_ON_ERROR);
            $state = $this->serializer->unserialize($payload);
        } catch (Throwable $exception) {
            throw UnableToRetrieveSnapshot::dueTo(previous: $exception);
        }

        return new Snapshot(
            $id,
            $aggregateRootVersion,
            $state
        );
    }
}
