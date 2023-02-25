<?php

declare(strict_types=1);

namespace Andreo\EventSauce\Snapshotting\Versioned;

use Andreo\EventSauce\Snapshotting\Aggregate\SnapshotState;
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
 * @template T of AggregateRootWithSnapshotting
 *
 * @implements AggregateRootRepositoryWithSnapshotting<T>
 */
final readonly class AggregateRootRepositoryWithVersionedSnapshotting implements AggregateRootRepositoryWithSnapshotting
{
    /**
     * @param class-string<T>            $aggregateRootClassName
     * @param AggregateRootRepository<T> $regularRepository
     */
    public function __construct(
        private string $aggregateRootClassName,
        private MessageRepository $messageRepository,
        private SnapshotRepository $snapshotRepository,
        private AggregateRootRepository $regularRepository,
        private SnapshotVersionInflector $snapshotVersionInflector = new InflectVersionFromReturnedTypeOfSnapshotStateCreationMethod(),
        private SnapshotVersionComparator $snapshotVersionComparator = new EqSnapshotVersionComparator(),
    ) {
    }

    public function retrieveFromSnapshot(AggregateRootId $aggregateRootId): object
    {
        $snapshot = $this->snapshotRepository->retrieve($aggregateRootId);
        if (!$snapshot instanceof Snapshot) {
            return $this->retrieve($aggregateRootId);
        }
        $state = $snapshot->state();
        assert($state instanceof SnapshotState);

        /** @var class-string<AggregateRootWithSnapshotting> $aggregateRootClassName */
        $aggregateRootClassName = $this->aggregateRootClassName;

        $snapshotVersion = $this->snapshotVersionInflector->snapshotVersion($aggregateRootClassName);
        if (!$this->snapshotVersionComparator->compare($snapshotVersion, $state->version())) {
            return $this->retrieve($aggregateRootId);
        }

        $events = $this->retrieveAllEventsAfterVersion($aggregateRootId, $snapshot->aggregateRootVersion());
        return $aggregateRootClassName::reconstituteFromSnapshotAndEvents($snapshot, $events);
    }

    /**
     * @param T $aggregateRoot
     */
    public function storeSnapshot(AggregateRootWithSnapshotting $aggregateRoot): void
    {
        $snapshot = $aggregateRoot->createSnapshot();
        $state = $snapshot->state();
        if (!$state instanceof SnapshotState || !$state->payload instanceof VersionedSnapshotState) {
            throw SnapshotIsNotVersioned::fromPayload($state);
        }

        $this->snapshotRepository->persist($snapshot);
    }

    /**
     * @return T
     */
    public function retrieve(AggregateRootId $aggregateRootId): object
    {
        return $this->regularRepository->retrieve($aggregateRootId);
    }

    /**
     * @param T $aggregateRoot
     */
    public function persist(object $aggregateRoot): void
    {
        $this->regularRepository->persist($aggregateRoot);
    }

    public function persistEvents(AggregateRootId $aggregateRootId, int $aggregateRootVersion, object ...$events): void
    {
        $this->regularRepository->persistEvents($aggregateRootId, $aggregateRootVersion, ...$events);
    }

    /**
     * @return Generator<int, object, void, int<0, max>>
     */
    private function retrieveAllEventsAfterVersion(AggregateRootId $aggregateRootId, int $version): Generator
    {
        /** @var Generator<int, object, void, int<0, max>> $messages */
        $messages = $this->messageRepository->retrieveAllAfterVersion($aggregateRootId, $version);

        /** @var Message $message */
        foreach ($messages as $message) {
            yield $message->payload();
        }

        return $messages->getReturn();
    }
}
