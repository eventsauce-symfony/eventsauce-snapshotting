<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting;

use Andreo\EventSauce\Snapshotting\Exception\InvalidArgumentException;
use EventSauce\EventSourcing\AggregateRootId;
use EventSauce\EventSourcing\AggregateRootRepository;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\MessageRepository;
use EventSauce\EventSourcing\Snapshotting\AggregateRootRepositoryWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\AggregateRootWithSnapshotting;
use EventSauce\EventSourcing\Snapshotting\Snapshot;
use EventSauce\EventSourcing\Snapshotting\SnapshotRepository;
use Generator;
use function assert;

/**
 * @implements AggregateRootRepositoryWithSnapshotting<AggregateRootWithVersionedSnapshotting>
 */
final class AggregateRootRepositoryWithVersionedSnapshotting implements AggregateRootRepositoryWithSnapshotting
{
    /**
     * @param class-string<AggregateRootWithVersionedSnapshotting> $aggregateRootClassName
     * @param AggregateRootRepository<AggregateRootWithVersionedSnapshotting> $regularRepository
     */
    public function __construct(
        private string $aggregateRootClassName,
        private MessageRepository $messageRepository,
        private SnapshotRepository $snapshotRepository,
        private AggregateRootRepository $regularRepository
    ) {}

    public function retrieveFromSnapshot(AggregateRootId $aggregateRootId): object
    {
        $snapshot = $this->snapshotRepository->retrieve($aggregateRootId);

        if (!$snapshot instanceof Snapshot) {
            return $this->retrieve($aggregateRootId);
        }
        $state = $snapshot->state();
        assert($state instanceof SnapshotState);

        /** @var class-string<AggregateRootWithVersionedSnapshotting> $className */
        $className = $this->aggregateRootClassName;
        if ($className::getSnapshotVersion() !== $state->schemaVersion()) {
            return $this->retrieve($aggregateRootId);
        }

        $events = $this->retrieveAllEventsAfterVersion($aggregateRootId, $snapshot->aggregateRootVersion());

        return $className::reconstituteFromSnapshotAndEvents($snapshot, $events);
    }

    public function storeSnapshot(AggregateRootWithSnapshotting $aggregateRoot): void
    {
        if (!$aggregateRoot instanceof AggregateRootWithVersionedSnapshotting) {
            throw InvalidArgumentException::aggregateMustBeVersioned();
        }

        $snapshot = $aggregateRoot->createSnapshot();
        $this->snapshotRepository->persist($snapshot);
    }

    private function retrieveAllEventsAfterVersion(AggregateRootId $aggregateRootId, int $version): Generator
    {
        /** @var Message[]|Generator $messages */
        $messages = $this->messageRepository->retrieveAllAfterVersion($aggregateRootId, $version);

        foreach ($messages as $message) {
            yield $message->event();
        }

        return $messages->getReturn();
    }

    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->regularRepository->retrieve($aggregateRootId);
    }

    public function persist(object $aggregateRoot): void
    {
        $this->regularRepository->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
    {
        $this->regularRepository->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }
}
